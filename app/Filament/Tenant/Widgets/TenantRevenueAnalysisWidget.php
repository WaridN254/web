<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Product;
use App\Services\BranchService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantRevenueAnalysisWidget extends Widget
{
    protected string $view = 'filament.tenant.widgets.tenant-revenue-analysis-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        $user = auth()->user();
        $tenantId = $user?->tenant_id;
        $cashier = $user?->scopedCashierId();
        $currency = $user?->tenant?->currency ?? 'UGX';
        $branchId = app(BranchService::class)->getActiveBranchId();

        $cacheKey = "tenant:{$tenantId}:widget_revenue";

        return Cache::remember($cacheKey, 300, function () use ($tenantId, $cashier, $currency, $branchId) {
            return $this->computeData($tenantId, $cashier, $currency, $branchId);
        });
    }

    protected function computeData($tenantId, $cashier, $currency, $branchId): array
    {

        $salesHourly = array_fill(0, 24, 0);
        $purchaseHourly = array_fill(0, 24, 0);
        $heatmap = array_fill(0, 6, array_fill(0, 7, 0));
        $topCategories = collect();
        $totalCats = 0;
        $totalProducts = 0;
        $topSelling = collect();
        $lowStock = collect();
        $recentSales = collect();
        $txSale = collect();
        $txPurchase = collect();
        $txQuotation = collect();
        $txExpenses = collect();
        $txInvoice = collect();
        $topCustomers = collect();
        $supplierCount = 0;
        $customerCount = 0;
        $orderCount = 0;
        $firstTimeCount = 0;
        $returnCount = 0;
        $monthlySalesData = [];

        try {
            $rows = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereDate('transaction_date', now()->toDateString())
                ->where('document_type', '!=', 'credit_note')->where('status', '!=', 'refunded')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("EXTRACT(HOUR FROM transaction_date) as h, COUNT(*) as c")
                ->groupByRaw("EXTRACT(HOUR FROM transaction_date)")->get();
            foreach ($rows as $r) { $salesHourly[(int) $r->h] = (int) $r->c; }
        } catch (\Exception $e) {}

        try {
            $rows = DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereDate('purchase_date', now()->toDateString())
                ->where('status', '!=', 'returned')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->selectRaw("EXTRACT(HOUR FROM purchase_date) as h, COUNT(*) as c")
                ->groupByRaw("EXTRACT(HOUR FROM purchase_date)")->get();
            foreach ($rows as $r) { $purchaseHourly[(int) $r->h] = (int) $r->c; }
        } catch (\Exception $e) {}

        try {
            $rows = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("EXTRACT(ISODOW FROM transaction_date) as dow, FLOOR(EXTRACT(HOUR FROM transaction_date) / 4) as bucket, COUNT(*) as c")
                ->groupByRaw("1, 2")->get();
            foreach ($rows as $r) {
                $b = min(5, max(0, (int) $r->bucket));
                $d = min(6, max(0, (int) $r->dow - 1));
                $heatmap[$b][$d] = (int) $r->c;
            }
        } catch (\Exception $e) {}

        try {
            $topCategories = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->leftJoin('products as p', 'ti.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->selectRaw("COALESCE(NULLIF(c.name, ''), 'Uncategorized') as name, SUM(ti.quantity) as qty")
                ->groupBy(DB::raw("COALESCE(NULLIF(c.name, ''), 'Uncategorized')"))
                ->orderByDesc('qty')->limit(4)->get();
        } catch (\Exception $e) {}

        try { $totalCats = DB::table('categories')->where('tenant_id', $tenantId)->where('is_deleted', false)->count(); } catch (\Exception $e) {}
        try { $totalProducts = DB::table('products')->where('tenant_id', $tenantId)->where('is_deleted', false)->count(); } catch (\Exception $e) {}

        try {
            $rows = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->select('ti.product_id', 'ti.product_name', DB::raw('SUM(ti.quantity) as total_sold'), DB::raw('AVG(ti.unit_price) as avg_price'))
                ->groupBy('ti.product_id', 'ti.product_name')
                ->orderByDesc('total_sold')->limit(5)->get();

            $productIds = $rows->pluck('product_id')->filter()->values()->all();
            $deltaMap = [];
            if (!empty($productIds)) {
                $deltaRows = DB::table('transaction_items as ti')
                    ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                    ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                    ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                    ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                    ->whereIn('ti.product_id', $productIds)
                    ->selectRaw("ti.product_id, COALESCE(SUM(CASE WHEN t.transaction_date >= now() - interval '7 days' THEN ti.quantity ELSE 0 END), 0) as cur, COALESCE(SUM(CASE WHEN t.transaction_date < now() - interval '7 days' AND t.transaction_date >= now() - interval '14 days' THEN ti.quantity ELSE 0 END), 0) as prev")
                    ->groupBy('ti.product_id')
                    ->get();
                foreach ($deltaRows as $dr) {
                    $deltaMap[$dr->product_id] = (float) $dr->prev > 0
                        ? round((((float) $dr->cur - (float) $dr->prev) / (float) $dr->prev) * 100, 1)
                        : null;
                }
            }

            foreach ($rows as $row) {
                $topSelling->push((object) [
                    'product_id' => $row->product_id,
                    'name' => $row->product_name,
                    'price' => $row->avg_price,
                    'total_sold' => $row->total_sold,
                    'delta' => $deltaMap[$row->product_id] ?? null,
                    'image' => null,
                ]);
            }
        } catch (\Exception $e) {}

        try {
            $lowStock = DB::table('products')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->select('name', 'id', 'current_stock')
                ->orderBy('current_stock')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $recentSales = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->leftJoin('products as p', 'ti.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->select('ti.product_id', 'ti.product_name', 'c.name as category_name', 'ti.unit_price', 't.transaction_date', 't.status', 't.receipt_number')
                ->orderByDesc('t.transaction_date')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $productIds = collect()
                ->merge($topSelling->pluck('product_id'))
                ->merge($lowStock->pluck('id'))
                ->merge($recentSales->pluck('product_id'))
                ->filter()
                ->unique()
                ->values();

            $imageMap = Product::query()
                ->with('images')
                ->whereIn('id', $productIds)
                ->get()
                ->mapWithKeys(fn ($product) => [$product->id => $product->imageUrl()])
                ->all();

            $topSelling = $topSelling->map(function ($row) use ($imageMap) {
                $row->image = $imageMap[$row->product_id] ?? null;
                return $row;
            });

            $lowStock = $lowStock->map(function ($row) use ($imageMap) {
                $row->image = $imageMap[$row->id] ?? null;
                return $row;
            });

            $recentSales = $recentSales->map(function ($row) use ($imageMap) {
                $row->image = $imageMap[$row->product_id] ?? null;
                return $row;
            });
        } catch (\Exception $e) {}

        try {
            $txSale = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->where('document_type', '!=', 'credit_note')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->select('receipt_number', 'customer_name', 'transaction_date', 'status', 'total_amount')
                ->orderByDesc('transaction_date')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $txPurchase = DB::table('purchase_orders as po')
                ->leftJoin('suppliers as s', 'po.supplier_id', '=', 's.id')
                ->where('po.tenant_id', $tenantId)->where('po.is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('po.branch_id', $branchId))
                ->select('po.purchase_number', 's.company_name as supplier', 'po.status', 'po.grand_total', 'po.purchase_date')
                ->orderByDesc('po.purchase_date')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $txQuotation = DB::table('quotations')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->select('quote_number', 'customer_name', 'status', 'total_amount', 'created_at')
                ->orderByDesc('created_at')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $txExpenses = DB::table('cash_movements')
                ->where('tenant_id', $tenantId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->select('reason', 'category', 'amount', 'movement_date')
                ->orderByDesc('movement_date')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $txInvoice = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->where(function ($q) { $q->where('balance_due', '>', 0)->orWhere('document_type', 'invoice'); })
                ->select('receipt_number', 'customer_name', 'transaction_date', 'balance_due', 'settlement_status')
                ->orderByDesc('transaction_date')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $topCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->select('full_name', 'address', 'phone', 'total_orders', 'total_spent')
                ->orderByDesc('total_spent')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $supplierCount = DB::table('suppliers')->where('tenant_id', $tenantId)->where('is_deleted', false)->count();
        } catch (\Exception $e) {}
        try {
            $customerCount = DB::table('customers')->where('tenant_id', $tenantId)->where('is_deleted', false)->count();
        } catch (\Exception $e) {}
        try {
            $orderCount = DB::table('transactions')->where('tenant_id', $tenantId)->where('is_deleted', false)->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))->count();
        } catch (\Exception $e) {}

        try {
            $branchCondition = $branchId ? ' AND branch_id = ?' : '';
            $cust = DB::select(
                "SELECT COUNT(*) FILTER (WHERE cnt = 1) AS first_time, COUNT(*) FILTER (WHERE cnt > 1) AS return_customers FROM (SELECT customer_id, COUNT(*) cnt FROM transactions WHERE tenant_id = ? AND is_deleted = false AND customer_id IS NOT NULL" . $branchCondition . ($cashier ? ' AND cashier_id = ?' : '') . " GROUP BY customer_id) x",
                array_merge([$tenantId], $branchId ? [$branchId] : [], $cashier ? [$cashier] : []),
            );
            $firstTimeCount = (int) ($cust[0]->first_time ?? 0);
            $returnCount = (int) ($cust[0]->return_customers ?? 0);
        } catch (\Exception $e) {}

        try {
            $monthlyData = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereYear('transaction_date', now()->year)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("EXTRACT(MONTH FROM transaction_date) as month, COALESCE(SUM(CASE WHEN status != 'refunded' THEN total_amount ELSE 0 END), 0) as revenue, COALESCE(SUM(CASE WHEN status = 'refunded' THEN total_amount ELSE 0 END), 0) as expense")
                ->groupByRaw('EXTRACT(MONTH FROM transaction_date)')
                ->orderByRaw('EXTRACT(MONTH FROM transaction_date)')
                ->get();
            foreach ($monthlyData as $row) {
                $monthlySalesData[(int) $row->month] = ['revenue' => (float) $row->revenue, 'expense' => (float) $row->expense];
            }
        } catch (\Exception $e) {}

        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartLabels = [];
        $chartRevenue = [];
        $chartExpense = [];
        foreach ($months as $i => $m) {
            $chartLabels[] = $m;
            $chartRevenue[] = $monthlySalesData[$i + 1]['revenue'] ?? 0;
            $chartExpense[] = -abs($monthlySalesData[$i + 1]['expense'] ?? 0);
        }
        $totalRevenue = array_sum($chartRevenue);
        $totalExpense = abs(array_sum($chartExpense));

        return [
            'currency' => $currency,
            'salesHourly' => $salesHourly,
            'purchaseHourly' => $purchaseHourly,
            'saleCountToday' => array_sum($salesHourly),
            'purchaseCountToday' => array_sum($purchaseHourly),
            'heatmap' => $heatmap,
            'topCategories' => $topCategories,
            'totalCats' => $totalCats,
            'totalProducts' => $totalProducts,
            'topSelling' => $topSelling,
            'lowStock' => $lowStock,
            'recentSales' => $recentSales,
            'txSale' => $txSale,
            'txPurchase' => $txPurchase,
            'txQuotation' => $txQuotation,
            'txExpenses' => $txExpenses,
            'txInvoice' => $txInvoice,
            'topCustomers' => $topCustomers,
            'supplierCount' => $supplierCount,
            'customerCount' => $customerCount,
            'orderCount' => $orderCount,
            'firstTimeCount' => $firstTimeCount,
            'returnCount' => $returnCount,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartExpense' => $chartExpense,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
        ];
    }
}