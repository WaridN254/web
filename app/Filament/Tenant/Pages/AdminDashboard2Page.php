<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AdminDashboard2Page extends Page
{
    protected static ?int $navigationSort = 2;


    public static function getNavigationLabel(): string
    {
        return __('navigation.admin_dashboard_2');
    }


    protected static ?string $title = null;

    protected static ?string $slug = 'admin-dashboard-2';

    public ?string $subheading = null;

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->can_access_dashboard_2 ?? true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.admin-dashboard-2';

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

        $purchaseDue = 0.0;
        $salesDue = 0.0;
        $totalSaleAmount = 0.0;
        $totalExpenses = 0.0;
        $customers = 0;
        $suppliers = 0;
        $purchaseInvoices = 0;
        $salesInvoices = 0;
        $salesByMonth = array_fill(1, 12, 0.0);
        $purchaseByMonth = array_fill(1, 12, 0.0);
        $recentProducts = collect();
        $expiredProducts = collect();

        try {
            $purchaseDue = (float) DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)->whereNull('cancelled_at')
                ->where('status', '!=', 'returned')->where('due_amount', '>', 0)
                ->sum('due_amount');
        } catch (\Exception $e) {}

        try {
            $salesDue = (float) DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->where('balance_due', '>', 0)
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->sum('balance_due');
        } catch (\Exception $e) {}

        try {
            $totalSaleAmount = (float) DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->where('document_type', '!=', 'credit_note')
                ->where('status', '!=', 'refunded')
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->sum('total_amount');
        } catch (\Exception $e) {}

        try {
            $totalExpenses = (float) DB::table('cash_movements')
                ->where('tenant_id', $tenantId)
                ->where('movement_type', 'cash_out')
                ->sum('amount');
        } catch (\Exception $e) {}

        try { $customers = DB::table('customers')->where('tenant_id', $tenantId)->where('is_deleted', false)->count(); } catch (\Exception $e) {}
        try { $suppliers = DB::table('suppliers')->where('tenant_id', $tenantId)->where('is_deleted', false)->count(); } catch (\Exception $e) {}
        try { $purchaseInvoices = DB::table('purchase_orders')->where('tenant_id', $tenantId)->whereNull('cancelled_at')->count(); } catch (\Exception $e) {}
        try {
            $salesInvoices = DB::table('transactions')->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->where('document_type', '!=', 'credit_note')->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))->count();
        } catch (\Exception $e) {}

        try {
            DB::table('transactions')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->where('status', '!=', 'refunded')->whereYear('transaction_date', $year)
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("EXTRACT(MONTH FROM transaction_date) as m, SUM(total_amount) as amt")
                ->groupByRaw("EXTRACT(MONTH FROM transaction_date)")
                ->get()->each(function ($r) use (&$salesByMonth) {
                    $salesByMonth[(int) $r->m] = (float) $r->amt;
                });
        } catch (\Exception $e) {}

        try {
            DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)->whereNull('cancelled_at')
                ->where('status', '!=', 'returned')->whereYear('purchase_date', $year)
                ->selectRaw("EXTRACT(MONTH FROM purchase_date) as m, SUM(grand_total) as amt")
                ->groupByRaw("EXTRACT(MONTH FROM purchase_date)")
                ->get()->each(function ($r) use (&$purchaseByMonth) {
                    $purchaseByMonth[(int) $r->m] = (float) $r->amt;
                });
        } catch (\Exception $e) {}

        try {
            $recentProducts = Product::query()
                ->with('images')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->latest('created_at')->limit(5)->get()
                ->map(fn ($p) => (object) [
                    'name' => $p->name,
                    'price' => (float) $p->billing_price,
                    'image' => $p->imageUrl(),
                ]);
        } catch (\Exception $e) {}

        try {
            $expiredProducts = DB::table('products')
                ->where('tenant_id', $tenantId)->where('is_deleted', false)
                ->whereNotNull('expiration_date')->where('expiration_date', '<', now())
                ->select('name', 'barcode', 'expiration_date', 'created_at')
                ->orderBy('expiration_date')->limit(5)->get();
        } catch (\Exception $e) {}

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartLabels = $months;
        $salesChart = [];
        $purchaseChart = [];
        foreach ($months as $i => $m) {
            $salesChart[] = $salesByMonth[$i + 1];
            $purchaseChart[] = $purchaseByMonth[$i + 1];
        }
        $maxChart = max(1, (float) max(array_merge($salesChart, $purchaseChart)));

        return [
            'currency' => $currency,
            'purchaseDue' => $purchaseDue,
            'salesDue' => $salesDue,
            'totalSaleAmount' => $totalSaleAmount,
            'totalExpenses' => $totalExpenses,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'purchaseInvoices' => $purchaseInvoices,
            'salesInvoices' => $salesInvoices,
            'chartLabels' => $chartLabels,
            'salesChart' => $salesChart,
            'purchaseChart' => $purchaseChart,
            'maxChart' => $maxChart,
            'recentProducts' => $recentProducts,
            'expiredProducts' => $expiredProducts,
        ];
    }
}