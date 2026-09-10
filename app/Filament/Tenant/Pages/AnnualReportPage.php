<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AnnualReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';


    public static function getNavigationLabel(): string
    {
        return __('navigation.reports');
    }


    protected static ?string $title = 'Annual Report';

    protected static ?string $slug = 'annual-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 19;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Yearly report';
    }

    protected function defaultFilters(): array
    {
        return ['year' => (string) now()->year];
    }

    public function getFilterFields(): array
    {
        $currentYear = now()->year;
        $years = [];
        for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++) {
            $years[$y] = (string) $y;
        }

        return array_merge($this->getBranchFilterField(), [
            ['type' => 'select', 'key' => 'year', 'label' => 'Year', 'options' => ['' => 'All Years'] + $years],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'month', 'label' => 'Month', 'type' => 'text'],
            ['key' => 'sales', 'label' => 'Sales', 'type' => 'money', 'align' => 'right'],
            ['key' => 'purchase', 'label' => 'Purchase', 'type' => 'money', 'align' => 'right'],
            ['key' => 'profit', 'label' => 'Profit', 'type' => 'money', 'align' => 'right'],
            ['key' => 'expense', 'label' => 'Expense', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $year = (int) ($this->filterValue('year') ?: now()->year);

        $sales = [];
        $purchases = [];
        $profits = [];
        $expenses = [];

        $branchId = $this->activeBranchId();

        $salesRows = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->whereNotIn('document_type', ['refund', 'return'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('transaction_date', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->selectRaw("TO_CHAR(transaction_date, 'MM') AS mm, SUM(total_amount) AS total")
            ->groupByRaw("TO_CHAR(transaction_date, 'MM')")
            ->get();
        foreach ($salesRows as $r) {
            $sales[(int) $r->mm] = (float) $r->total;
        }

        $purchaseRows = DB::table('purchase_orders')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('purchase_date', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->selectRaw("TO_CHAR(purchase_date, 'MM') AS mm, SUM(grand_total) AS total")
            ->groupByRaw("TO_CHAR(purchase_date, 'MM')")
            ->get();
        foreach ($purchaseRows as $r) {
            $purchases[(int) $r->mm] = (float) $r->total;
        }

        $profitRows = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transaction_items.tenant_id', $tenantId)
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->where('transactions.is_deleted', false)
            ->when($branchId, fn ($q) => $q->where('transactions.branch_id', $branchId))
            ->whereBetween('transactions.transaction_date', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->selectRaw("TO_CHAR(transactions.transaction_date, 'MM') AS mm, SUM(transaction_items.line_total - (COALESCE(transaction_items.cost_price, 0) * transaction_items.quantity)) AS total")
            ->groupByRaw("TO_CHAR(transactions.transaction_date, 'MM')")
            ->get();
        foreach ($profitRows as $r) {
            $profits[(int) $r->mm] = (float) $r->total;
        }

        $cashExpenseRows = DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('movement_date', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->selectRaw("TO_CHAR(movement_date, 'MM') AS mm, SUM(amount) AS total")
            ->groupByRaw("TO_CHAR(movement_date, 'MM')")
            ->get();
        foreach ($cashExpenseRows as $r) {
            $expenses[(int) $r->mm] = ($expenses[(int) $r->mm] ?? 0) + (float) $r->total;
        }

        $purchasePaymentRows = DB::table('purchase_payments')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('payment_date', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->selectRaw("TO_CHAR(payment_date, 'MM') AS mm, SUM(amount) AS total")
            ->groupByRaw("TO_CHAR(payment_date, 'MM')")
            ->get();
        foreach ($purchasePaymentRows as $r) {
            $expenses[(int) $r->mm] = ($expenses[(int) $r->mm] ?? 0) + (float) $r->total;
        }

        $rows = [];
        foreach (range(1, 12) as $m) {
            $rows[] = [
                'month' => Carbon::create($year, $m, 1)->format('F'),
                'sales' => $sales[$m] ?? 0,
                'purchase' => $purchases[$m] ?? 0,
                'profit' => $profits[$m] ?? 0,
                'expense' => $expenses[$m] ?? 0,
            ];
        }

        return $rows;
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'sales' => array_sum(array_column($rows, 'sales')),
                'purchase' => array_sum(array_column($rows, 'purchase')),
                'profit' => array_sum(array_column($rows, 'profit')),
                'expense' => array_sum(array_column($rows, 'expense')),
            ],
        ];
    }
}