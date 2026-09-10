<?php

namespace App\Services;

use App\Models\BranchStock;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get stock for a product at a specific branch.
     */
    public function getProductStock(string $productId, string $branchId, ?string $variantId = null): float
    {
        $record = BranchStock::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($record) {
            return (float) $record->quantity;
        }

        // Fallback: use product's own current_stock when no branch stock record exists
        if (is_null($variantId)) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                return (float) ($product->current_stock ?? 0);
            }
        }

        return 0.0;
    }

    /**
     * Get total stock across all branches for a product.
     */
    public function getProductStockAcrossBranches(string $productId, ?string $variantId = null): float
    {
        return (float) BranchStock::where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->sum('quantity');
    }

    /**
     * Check if a branch has enough stock.
     */
    public function hasEnoughStock(string $productId, string $branchId, float $quantity, ?string $variantId = null): bool
    {
        return $this->getProductStock($productId, $branchId, $variantId) >= $quantity;
    }

    /**
     * Increment stock at a branch. Creates the branch_stock record if it doesn't exist.
     */
    public function incrementStock(
        string $productId,
        string $branchId,
        float $quantity,
        ?string $variantId = null,
        ?string $tenantId = null,
    ): void {
        $branchStock = BranchStock::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ],
            ['quantity' => 0]
        );

        $branchStock->quantity += $quantity;
        $branchStock->save();

        $this->syncProductCurrentStock($productId);
    }

    /**
     * Decrement stock at a branch. Returns false if insufficient stock.
     */
    public function decrementStock(
        string $productId,
        string $branchId,
        float $quantity,
        ?string $variantId = null,
        ?string $tenantId = null,
    ): bool {
        $branchStock = BranchStock::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ],
            ['quantity' => 0]
        );

        if ((float) $branchStock->quantity < $quantity) {
            return false;
        }

        $branchStock->quantity -= $quantity;
        $branchStock->save();

        $this->syncProductCurrentStock($productId);

        return true;
    }

    /**
     * Set absolute stock at a branch.
     */
    public function setStock(
        string $productId,
        string $branchId,
        float $quantity,
        ?string $variantId = null,
        ?string $tenantId = null,
    ): void {
        $branchStock = BranchStock::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ],
            ['quantity' => 0]
        );

        $branchStock->quantity = $quantity;
        $branchStock->save();

        $this->syncProductCurrentStock($productId);
    }

    /**
     * Recompute products.current_stock from branch_stocks.
     * This keeps the global counter as a cached aggregate for backward compatibility.
     */
    public function syncProductCurrentStock(string $productId): void
    {
        $allStocks = BranchStock::where('product_id', $productId)
            ->select('variant_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('variant_id')
            ->get();

        $variantStocks = $allStocks->keyBy(fn ($s) => $s->variant_id ?? '__no_variant__');

        $totalStock = (float) ($variantStocks->get('__no_variant__')?->total_quantity ?? 0);
        Product::where('id', $productId)->update(['current_stock' => $totalStock]);

        $variantIds = ProductVariant::where('product_id', $productId)->pluck('id');
        if ($variantIds->isEmpty()) {
            $this->clearDashboardCaches($productId);

            return;
        }

        $cases = [];
        foreach ($variantIds as $variantId) {
            $stock = (float) ($variantStocks->get($variantId)?->total_quantity ?? 0);
            $cases[] = "WHEN id = '$variantId' THEN $stock";
        }

        ProductVariant::where('product_id', $productId)
            ->update(['current_stock' => DB::raw('CASE ' . implode(' ', $cases) . ' END')]);

        $this->clearDashboardCaches($productId);
    }

    /**
     * Clear dashboard widget caches for a product's tenant.
     */
    public function clearDashboardCaches(?string $productId = null): void
    {
        $tenantId = $productId
            ? (Product::find($productId)?->tenant_id ?? auth()->user()?->tenant_id)
            : auth()->user()?->tenant_id;

        if (! $tenantId) {
            return;
        }

        Cache::forget("tenant:{$tenantId}:widget_stats");
        Cache::forget("tenant:{$tenantId}:widget_revenue");
        Cache::forget("tenant:{$tenantId}:widget_sales_dashboard");
    }

    /**
     * Sync all products' current_stock from branch_stocks (bulk operation).
     * Use during migration or maintenance.
     */
    public function syncAllCurrentStock(): void
    {
        $productIds = BranchStock::select('product_id')
            ->distinct()
            ->pluck('product_id');

        foreach ($productIds as $productId) {
            $this->syncProductCurrentStock($productId);
        }
    }

    /**
     * Get variant stock at a branch.
     */
    public function getVariantStock(string $variantId, string $branchId): float
    {
        return $this->getProductStock(
            ProductVariant::find($variantId)?->product_id,
            $branchId,
            $variantId
        );
    }

    /**
     * Get stock summary across all branches for a product.
     */
    public function getStockSummary(string $productId): array
    {
        $records = BranchStock::where('product_id', $productId)
            ->with('branch:id,name')
            ->get();

        $summary = [];
        $total = 0;

        foreach ($records as $record) {
            $qty = (float) $record->quantity;
            $summary[$record->branch_id] = [
                'branch_name' => $record->branch?->name ?? 'Unknown',
                'quantity' => $qty,
            ];
            $total += $qty;
        }

        return [
            'branches' => $summary,
            'total' => $total,
        ];
    }
}
