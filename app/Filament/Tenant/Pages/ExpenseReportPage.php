<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ExpenseReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';


    public static function getNavigationLabel(): string
    {
        return __('navigation.expense_report');
    }


    protected static ?string $title = 'Expense Report';

    protected static ?string $slug = 'expense-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'All expenses report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'category' => ''];
    }

    public function getFilterFields(): array
    {
        $categories = DB::table('cash_movements')
            ->where('tenant_id', $this->tenantId())
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category', 'category')
            ->all();

        $categories = array_merge(['' => 'All Categories', 'Purchase' => 'Purchase'], $categories);

        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            ['type' => 'select', 'key' => 'category', 'label' => 'Category', 'options' => $categories],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'expense_name', 'label' => 'Expense Name', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Paid' => 'green', 'Completed' => 'green', 'Pending' => 'amber', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $category = $this->filterValue('category');

        $rows = [];

        $movementsQuery = DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->when($from, fn ($q) => $q->where('movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('movement_date', '<=', $to . ' 23:59:59'))
            ->when($category, fn ($q) => $q->where('category', $category));
        $this->scopeQueryToBranch($movementsQuery, 'branch_id');
        $movements = $movementsQuery->get();

        foreach ($movements as $m) {
            $rows[] = [
                'expense_name' => $m->reason ?: 'Cash Out',
                'category' => $m->category ?: 'General',
                'description' => $m->reason ?: 'Cash out movement',
                'date' => $m->movement_date,
                'amount' => (float) $m->amount,
                'status' => 'Completed',
            ];
        }

        $paymentsQuery = DB::table('purchase_payments as pp')
            ->join('purchase_orders as po', 'pp.purchase_id', '=', 'po.id')
            ->join('suppliers as s', 'pp.supplier_id', '=', 's.id')
            ->where('pp.tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('pp.payment_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('pp.payment_date', '<=', $to . ' 23:59:59'))
            ->when($category === 'Purchase', fn ($q) => $q->whereRaw('1 = 1'))
            ->selectRaw("
                po.purchase_number AS reference,
                s.company_name AS supplier,
                pp.amount,
                pp.payment_date,
                pp.notes
            ");
        $this->scopeQueryToBranch($paymentsQuery, 'po.branch_id');
        $payments = $paymentsQuery->get();

        if ($category === '' || $category === 'Purchase') {
            foreach ($payments as $p) {
                $rows[] = [
                    'expense_name' => 'Purchase Payment - ' . $p->reference,
                    'category' => 'Purchase',
                    'description' => $p->supplier . ($p->notes ? ' - ' . $p->notes : ''),
                    'date' => $p->payment_date,
                    'amount' => (float) $p->amount,
                    'status' => 'Paid',
                ];
            }
        }

        usort($rows, fn ($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

        return $rows;
    }

    protected function getRows(): array
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        return $this->cachedRows = $this->getReportRows();
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getRows();

        return [
            'label' => 'Total',
            'values' => [
                'amount' => array_sum(array_column($rows, 'amount')),
            ],
        ];
    }
}