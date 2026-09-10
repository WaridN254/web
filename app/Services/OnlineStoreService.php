<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\OnlineStoreSettings;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OnlineStoreService
{
    public function getSettings(string $tenantId): ?OnlineStoreSettings
    {
        return OnlineStoreSettings::where('tenant_id', $tenantId)->first();
    }

    public function getOrCreateSettings(string $tenantId): OnlineStoreSettings
    {
        return OnlineStoreSettings::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'is_enabled' => false,
                'store_name' => 'Online Store',
                'fulfillment_strategy' => 'branch_with_stock',
                'allow_pickup' => true,
                'allow_delivery' => false,
            ]
        );
    }

    public function isStoreEnabled(string $tenantId): bool
    {
        $settings = $this->getSettings($tenantId);
        return $settings?->is_enabled ?? false;
    }

    public function getStorefrontProducts(string $tenantId, ?string $categoryId = null, ?string $search = null, int $page = 1, int $perPage = 12): array
    {
        $query = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('barcode', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $total = $query->count();
        $products = $query->orderBy('name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($product) => $this->formatProductForStorefront($product, $tenantId));

        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function getStorefrontProduct(string $tenantId, string $productId): ?array
    {
        $product = Product::where('tenant_id', $tenantId)
            ->where('id', $productId)
            ->where('is_deleted', false)
            ->first();

        if (!$product) {
            return null;
        }

        return $this->formatProductForStorefront($product, $tenantId, true);
    }

    private function formatProductForStorefront(Product $product, string $tenantId, bool $detail = false): array
    {
        $totalStock = $product->track_stock
            ? (float) app(InventoryService::class)->getProductStockAcrossBranches($product->id)
            : null;

        $availableBranches = [];
        if ($product->track_stock) {
            $branchStocks = DB::table('branch_stocks')
                ->join('branches', 'branch_stocks.branch_id', '=', 'branches.id')
                ->where('branch_stocks.tenant_id', $tenantId)
                ->where('branch_stocks.product_id', $product->id)
                ->where('branches.is_active', true)
                ->select('branches.id as branch_id', 'branches.name as branch_name', 'branch_stocks.quantity', 'branch_stocks.reserved_quantity')
                ->get();

            foreach ($branchStocks as $bs) {
                $available = (float) $bs->quantity - (float) $bs->reserved_quantity;
                if ($available > 0) {
                    $availableBranches[] = [
                        'branch_id' => $bs->branch_id,
                        'branch_name' => $bs->branch_name,
                        'quantity' => $available,
                    ];
                }
            }
        }

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'barcode' => $product->barcode,
            'sku' => $product->sku,
            'price' => (float) $product->billing_price,
            'image_url' => $product->imageUrl(),
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'track_stock' => $product->track_stock,
            'in_stock' => !$product->track_stock || $totalStock > 0,
            'stock_quantity' => $totalStock,
            'available_branches' => $availableBranches,
        ];

        if ($detail && $product->has_variants) {
            $data['variants'] = $product->variants
                ->where('is_active', true)
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->combination_name,
                    'price' => (float) $v->effective_selling_price,
                    'sku' => $v->sku,
                    'in_stock' => !$product->track_stock || (float) $v->available_stock > 0,
                    'stock_quantity' => (float) $v->available_stock,
                ])
                ->values();
        }

        if ($detail && $product->saleUnits()->count() > 0) {
            $data['sale_units'] = $product->saleUnits()->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->unit_name,
                'label' => $u->unit_label,
                'conversion' => (float) $u->conversion_to_base_unit,
                'price' => (float) $u->price,
                'is_default' => $u->is_default,
            ])->values();
        }

        return $data;
    }

    public function getCategories(string $tenantId): array
    {
        return \App\Models\Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    public function getActiveBranches(string $tenantId): array
    {
        $settings = $this->getSettings($tenantId);

        $query = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($settings && $settings->pickup_branches) {
            $query->whereIn('id', $settings->pickup_branches);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'address' => $b->address])
            ->toArray();
    }

    public function determineFulfillmentBranch(string $tenantId, array $cartItems): ?string
    {
        $settings = $this->getSettings($tenantId);
        $strategy = $settings?->fulfillment_strategy ?? 'branch_with_stock';

        return match ($strategy) {
            'branch_with_stock' => $this->findBranchWithStock($tenantId, $cartItems),
            'highest_stock' => $this->findHighestStockBranch($tenantId, $cartItems),
            default => $this->findBranchWithStock($tenantId, $cartItems),
        };
    }

    private function findBranchWithStock(string $tenantId, array $cartItems): ?string
    {
        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($branches as $branch) {
            $allAvailable = true;
            foreach ($cartItems as $item) {
                $stock = app(InventoryService::class)->hasEnoughStock(
                    $item['product_id'],
                    $branch->id,
                    $item['quantity'] * ($item['unit_conversion_to_base'] ?? 1),
                    $item['variant_id'] ?? null
                );
                if (!$stock) {
                    $allAvailable = false;
                    break;
                }
            }
            if ($allAvailable) {
                return $branch->id;
            }
        }

        return null;
    }

    private function findHighestStockBranch(string $tenantId, array $cartItems): ?string
    {
        $bestBranch = null;
        $bestScore = -1;

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($branches as $branch) {
            $score = 0;
            $allAvailable = true;
            foreach ($cartItems as $item) {
                $stock = app(InventoryService::class)->getProductStock(
                    $item['product_id'],
                    $branch->id,
                    $item['variant_id'] ?? null
                );
                if ($stock < $item['quantity']) {
                    $allAvailable = false;
                    break;
                }
                $score += $stock;
            }
            if ($allAvailable && $score > $bestScore) {
                $bestScore = $score;
                $bestBranch = $branch->id;
            }
        }

        return $bestBranch;
    }
}
