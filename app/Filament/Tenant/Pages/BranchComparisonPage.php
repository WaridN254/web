<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class BranchComparisonPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $title = 'Branch Comparison';

    protected static ?string $slug = 'branch-comparison';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getNavigationLabel(): string
    {
        return 'Branch Comparison';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }

    public static function getSubtitle(): string
    {
        return 'Compare performance across all branches';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (in_array($user->role?->name ?? '', ['Owner', 'Admin'], true)) {
            return true;
        }

        return (bool) ($user->can_view_all_branches ?? false);
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => ''];
    }

    public function getFilterFields(): array
    {
        return [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
        ];
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'branch', 'label' => 'Branch', 'type' => 'text'],
            ['key' => 'total_sales', 'label' => 'Total Sales', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total_paid', 'label' => 'Total Paid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total_unpaid', 'label' => 'Unpaid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'transaction_count', 'label' => 'Transactions', 'type' => 'number', 'align' => 'right'],
            ['key' => 'avg_sale', 'label' => 'Avg Sale', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total_expenses', 'label' => 'Expenses', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total_purchases', 'label' => 'Purchases', 'type' => 'money', 'align' => 'right'],
            ['key' => 'stock_value', 'label' => 'Stock Value', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $salesQuery = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->whereNotIn('document_type', ['refund', 'return'])
            ->when($from, fn ($q) => $q->where('transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('transaction_date', '<=', $to . ' 23:59:59'))
            ->groupBy('branch_id')
            ->selectRaw("
                branch_id,
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COALESCE(SUM(amount_paid), 0) AS total_paid,
                COALESCE(SUM(balance_due), 0) AS total_unpaid,
                COUNT(*) AS transaction_count
            ");

        $salesByBranch = [];
        foreach ($salesQuery->get() as $r) {
            $salesByBranch[$r->branch_id] = [
                'total_sales' => (float) $r->total_sales,
                'total_paid' => (float) $r->total_paid,
                'total_unpaid' => (float) $r->total_unpaid,
                'transaction_count' => (int) $r->transaction_count,
            ];
        }

        $expensesQuery = DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->when($from, fn ($q) => $q->where('movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('movement_date', '<=', $to . ' 23:59:59'))
            ->groupBy('branch_id')
            ->selectRaw("branch_id, COALESCE(SUM(amount), 0) AS total_expenses");

        $expensesByBranch = [];
        foreach ($expensesQuery->get() as $r) {
            $expensesByBranch[$r->branch_id] = (float) $r->total_expenses;
        }

        $purchasesQuery = DB::table('purchase_orders')
            ->where('tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('purchase_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('purchase_date', '<=', $to . ' 23:59:59'))
            ->groupBy('branch_id')
            ->selectRaw("branch_id, COALESCE(SUM(grand_total), 0) AS total_purchases");

        $purchasesByBranch = [];
        foreach ($purchasesQuery->get() as $r) {
            $purchasesByBranch[$r->branch_id] = (float) $r->total_purchases;
        }

        $stockQuery = DB::table('branch_stocks as bs')
            ->join('products as p', 'bs.product_id', '=', 'p.id')
            ->where('p.is_deleted', false)
            ->groupBy('bs.branch_id')
            ->selectRaw("bs.branch_id, COALESCE(SUM(bs.quantity * p.billing_price), 0) AS stock_value");

        $stockByBranch = [];
        foreach ($stockQuery->get() as $r) {
            $stockByBranch[$r->branch_id] = (float) $r->stock_value;
        }

        $branches = DB::table('branches')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($branches as $branch) {
            $branchId = $branch->id;
            $sales = $salesByBranch[$branchId] ?? ['total_sales' => 0, 'total_paid' => 0, 'total_unpaid' => 0, 'transaction_count' => 0];
            $transactionCount = $sales['transaction_count'];

            $rows[] = [
                'branch' => $branch->name,
                'total_sales' => $sales['total_sales'],
                'total_paid' => $sales['total_paid'],
                'total_unpaid' => $sales['total_unpaid'],
                'transaction_count' => $transactionCount,
                'avg_sale' => $transactionCount > 0 ? $sales['total_sales'] / $transactionCount : 0,
                'total_expenses' => $expensesByBranch[$branchId] ?? 0,
                'total_purchases' => $purchasesByBranch[$branchId] ?? 0,
                'stock_value' => $stockByBranch[$branchId] ?? 0,
            ];
        }

        return $rows;
    }

    protected function getRows(): array
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        return $this->cachedRows = $this->getReportRows();
    }

    public function getStatCards(): array
    {
        $rows = $this->getRows();
        $fmt = fn ($v) => number_format($v, 2);

        $totalSales = array_sum(array_column($rows, 'total_sales'));
        $totalExpenses = array_sum(array_column($rows, 'total_expenses'));
        $totalStock = array_sum(array_column($rows, 'stock_value'));
        $branchCount = count($rows);

        return [
            ['label' => 'Total Sales (All)', 'value' => $fmt($totalSales), 'color' => 'blue', 'icon' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>'],
            ['label' => 'Total Expenses (All)', 'value' => $fmt($totalExpenses), 'color' => 'red', 'icon' => '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
            ['label' => 'Total Stock Value', 'value' => $fmt($totalStock), 'color' => 'green', 'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
            ['label' => 'Active Branches', 'value' => $branchCount, 'color' => 'purple', 'icon' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>'],
        ];
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getRows();

        return [
            'label' => 'Total (All Branches)',
            'values' => [
                'total_sales' => array_sum(array_column($rows, 'total_sales')),
                'total_paid' => array_sum(array_column($rows, 'total_paid')),
                'total_unpaid' => array_sum(array_column($rows, 'total_unpaid')),
                'transaction_count' => array_sum(array_column($rows, 'transaction_count')),
                'avg_sale' => array_sum(array_column($rows, 'transaction_count')) > 0
                    ? array_sum(array_column($rows, 'total_sales')) / array_sum(array_column($rows, 'transaction_count'))
                    : 0,
                'total_expenses' => array_sum(array_column($rows, 'total_expenses')),
                'total_purchases' => array_sum(array_column($rows, 'total_purchases')),
                'stock_value' => array_sum(array_column($rows, 'stock_value')),
            ],
        ];
    }
}
