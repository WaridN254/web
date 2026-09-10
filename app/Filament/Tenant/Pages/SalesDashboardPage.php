<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesDashboardPage extends Page
{
    protected static ?int $navigationSort = 3;


    public static function getNavigationLabel(): string
    {
        return __('navigation.sales_dashboard');
    }


    protected static ?string $title = null;

    protected static ?string $slug = 'sales-dashboard';

    public ?string $subheading = null;

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->can_access_sales_dashboard ?? true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.sales-dashboard';

    public function currency(): string
    {
        return auth()->user()?->tenant?->currency_code
            ?? auth()->user()?->tenant?->settings['currency']
            ?? 'UGX';
    }

    public function data(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $cashier = auth()->user()?->scopedCashierId();
        $currency = $this->currency();
        $year = now()->year;

        $cacheKey = "tenant:{$tenantId}:widget_sales_dashboard";

        return Cache::remember($cacheKey, 300, function () use ($tenantId, $cashier, $currency, $year) {
            return $this->computeData($tenantId, $cashier, $currency, $year);
        });
    }

    protected function computeData($tenantId, $cashier, $currency, $year): array
    {

        $weeklyEarning = 0.0;
        $previousEarning = 0.0;
        $weeklyPct = null;
        $totalSales = 0;
        $totalPurchases = 0;
        $bestSellers = collect();
        $recentTransactions = collect();
        $revenueByMonth = array_fill(1, 12, 0.0);
        $expenseByMonth = array_fill(1, 12, 0.0);
        $topCategories = collect();
        $totalCats = 0;

        $weekStart = now()->startOfWeek();
        $prevWeekStart = now()->subWeek()->startOfWeek();

        try {
            $weeklyEarning = (float) DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereNotIn('document_type', ['credit_note', 'refund', 'return'])
                ->where('status', 'completed')
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->where('transaction_date', '>=', $weekStart)
                ->sum('total_amount');
        } catch (\Exception $e) {}

        try {
            $previousEarning = (float) DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereNotIn('document_type', ['credit_note', 'refund', 'return'])
                ->where('status', 'completed')
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->whereBetween('transaction_date', [$prevWeekStart, $weekStart])
                ->sum('total_amount');
        } catch (\Exception $e) {}

        if ($previousEarning > 0) {
            $weeklyPct = round((($weeklyEarning - $previousEarning) / $previousEarning) * 100);
        }

        try { $totalSales = DB::table('transactions')->where('tenant_id', $tenantId)->where('is_deleted', false)->whereNotIn('document_type', ['credit_note', 'refund', 'return'])->where('status', 'completed')->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))->count(); } catch (\Exception $e) {}
        try { $totalPurchases = DB::table('purchase_orders')->where('tenant_id', $tenantId)->whereNull('cancelled_at')->count(); } catch (\Exception $e) {}

        try {
            $bestSellers = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                ->whereNotIn('t.document_type', ['credit_note', 'refund', 'return'])
                ->where('t.status', 'completed')
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->select('ti.product_id', 'ti.product_name', DB::raw('SUM(ti.quantity) as total_sold'), DB::raw('AVG(ti.unit_price) as avg_price'))
                ->groupBy('ti.product_id', 'ti.product_name')
                ->orderByDesc('total_sold')->limit(5)->get();
        } catch (\Exception $e) {}

        try {
            $recentTransactions = DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereNotIn('document_type', ['credit_note', 'refund', 'return'])
                ->where('status', 'completed')
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->select('receipt_number', 'customer_name', 'transaction_date', 'payment_method', 'status', 'total_amount')
                ->orderByDesc('transaction_date')->limit(5)->get();
        } catch (\Exception $e) {}

        $recentTransactionItems = [];
        try {
            $receiptNumbers = $recentTransactions->pluck('receipt_number')->filter()->values();
            if ($receiptNumbers->isNotEmpty()) {
                $allItems = DB::table('transaction_items as ti')
                    ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                    ->whereIn('t.receipt_number', $receiptNumbers)
                    ->where('ti.is_deleted', false)
                    ->select('t.receipt_number', 'ti.product_name', 'ti.product_id')
                    ->orderBy('ti.sort_order')
                    ->get()
                    ->groupBy('receipt_number');

                $productIds = $allItems->flatten()->pluck('product_id')->filter()->unique()->values();
                $imageMap = [];
                if ($productIds->isNotEmpty()) {
                    $imageMap = Product::query()
                        ->with('images')
                        ->whereIn('id', $productIds)
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => $p->imageUrl()])
                        ->all();
                }

                foreach ($allItems as $rn => $items) {
                    $first = $items->first();
                    $recentTransactionItems[$rn] = (object) [
                        'product_name' => $first->product_name,
                        'image' => $imageMap[$first->product_id] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {}

        try {
            DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereYear('transaction_date', $year)
                ->where(function ($query) {
                    $query->whereNotIn('document_type', ['credit_note', 'refund', 'return'])
                        ->orWhere('status', 'refunded');
                })
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("EXTRACT(MONTH FROM transaction_date) as m, COALESCE(SUM(CASE WHEN document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN total_amount ELSE 0 END), 0) as revenue, COALESCE(SUM(CASE WHEN status = 'refunded' OR document_type IN ('refund', 'return') THEN total_amount ELSE 0 END), 0) as expense")
                ->groupByRaw("EXTRACT(MONTH FROM transaction_date)")
                ->get()->each(function ($r) use (&$revenueByMonth, &$expenseByMonth) {
                    $revenueByMonth[(int) $r->m] = (float) $r->revenue;
                    $expenseByMonth[(int) $r->m] = (float) $r->expense;
                });
        } catch (\Exception $e) {}

        try {
            $topCategories = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->leftJoin('products as p', 'ti.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                ->where('t.tenant_id', $tenantId)->where('t.is_deleted', false)
                ->whereNotIn('t.document_type', ['credit_note', 'refund', 'return'])
                ->where('t.status', 'completed')
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->selectRaw("COALESCE(NULLIF(c.name, ''), 'Uncategorized') as name, SUM(ti.quantity) as qty")
                ->groupBy(DB::raw("COALESCE(NULLIF(c.name, ''), 'Uncategorized')"))
                ->orderByDesc('qty')->limit(4)->get();
        } catch (\Exception $e) {}

        try { $totalCats = DB::table('categories')->where('tenant_id', $tenantId)->where('is_deleted', false)->count(); } catch (\Exception $e) {}

        try {
            $productIds = $bestSellers->pluck('product_id')->filter()->unique()->values();
            $imageMap = Product::query()
                ->with('images')
                ->whereIn('id', $productIds)
                ->get()
                ->mapWithKeys(fn ($p) => [$p->id => $p->imageUrl()])
                ->all();
            $bestSellers = $bestSellers->map(function ($row) use ($imageMap) {
                $row->image = $imageMap[$row->product_id] ?? null;
                return $row;
            });
        } catch (\Exception $e) {}

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartLabels = $months;
        $revenueChart = [];
        $expenseChart = [];
        foreach ($months as $i => $m) {
            $revenueChart[] = $revenueByMonth[$i + 1];
            $expenseChart[] = -abs($expenseByMonth[$i + 1]);
        }
        $totalRevenue = array_sum($revenueChart);
        $totalExpense = abs(array_sum($expenseChart));
        $maxAnalytics = max(1, (float) max(array_merge($revenueChart, array_map('abs', $expenseChart))));

        return [
            'currency' => $currency,
            'userName' => (auth()->user()?->name ?? '') ?: 'there',
            'weeklyEarning' => $weeklyEarning,
            'weeklyPct' => $weeklyPct,
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'bestSellers' => $bestSellers,
            'recentTransactions' => $recentTransactions,
            'recentTransactionItems' => $recentTransactionItems,
            'chartLabels' => $chartLabels,
            'revenueChart' => $revenueChart,
            'returnsChart' => $expenseChart,
            'expenseChart' => $expenseChart,
            'maxAnalytics' => $maxAnalytics,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'topCategories' => $topCategories,
            'totalCats' => $totalCats,
        ];
    }
}