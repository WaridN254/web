<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreditOutstandingReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';


    public static function getNavigationLabel(): string
    {
        return __('navigation.reports');
    }


    protected static ?string $title = 'Outstanding Credit';

    protected static ?string $slug = 'credit-outstanding-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Open credit and layaway invoices with balances due';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'customer' => '', 'status' => '', 'type' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'customer',
                'label' => 'Customer',
                'options' => ['' => 'All Customers'] + DB::table('customers')
                    ->where('tenant_id', $this->tenantId())
                    ->where('is_deleted', false)
                    ->orderBy('full_name')
                    ->pluck('full_name', 'id')
                    ->all(),
            ],
            [
                'type' => 'select',
                'key' => 'status',
                'label' => 'Status',
                'options' => ['' => 'All Statuses', 'overdue' => 'Overdue', 'current' => 'Current'],
            ],
            [
                'type' => 'select',
                'key' => 'type',
                'label' => 'Type',
                'options' => ['' => 'All Types', 'credit' => 'Credit', 'layaway' => 'Layaway'],
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'invoice', 'label' => 'Invoice', 'type' => 'text'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Sale Date', 'type' => 'date'],
            ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'badge', 'dot' => true, 'colors' => ['credit' => 'cyan', 'layaway' => 'purple', 'default' => 'secondary']],
            ['key' => 'total', 'label' => 'Total', 'type' => 'money', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Paid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money', 'align' => 'right'],
            ['key' => 'days', 'label' => 'Days Due', 'type' => 'number', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Overdue' => 'red', 'Current' => 'green', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $customer = $this->filterValue('customer');
        $status = $this->filterValue('status');
        $type = $this->filterValue('type');

        $query = DB::table('transactions as t')
            ->leftJoin('customers as c', 't.customer_id', '=', 'c.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('t.settlement_status', 'partial')
            ->where('t.balance_due', '>', 0)
            ->whereNotNull('t.customer_id')
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->when($customer, fn ($q) => $q->where('t.customer_id', $customer))
            ->when($type, fn ($q) => $q->where('t.transaction_type', $type))
            ->selectRaw("
                t.receipt_number AS invoice,
                COALESCE(c.full_name, t.customer_name, 'Customer') AS customer,
                t.transaction_date AS date,
                t.due_date AS due_date,
                COALESCE(t.transaction_type, CASE WHEN EXISTS (SELECT 1 FROM layaway_plans lp WHERE lp.transaction_id = t.id) THEN 'layaway' ELSE 'credit' END) AS type,
                t.total_amount AS total,
                t.amount_paid AS paid,
                t.balance_due AS outstanding,
                EXTRACT(EPOCH FROM (CURRENT_DATE - COALESCE(t.due_date, t.transaction_date))) / 86400 AS days,
                CASE WHEN COALESCE(t.due_date, t.transaction_date) < CURRENT_DATE THEN 'Overdue' ELSE 'Current' END AS status
            ");

        $this->scopeQueryToBranch($query, 't.branch_id');

        $rows = $query->get()
            ->map(function ($r) {
                $r = (array) $r;
                $r['days'] = max(0, (int) round((float) $r['days']));

                return $r;
            })
            ->all();

        if ($status === 'overdue') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['status'] === 'Overdue'));
        } elseif ($status === 'current') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['status'] === 'Current'));
        }

        usort($rows, fn ($a, $b) => $b['days'] <=> $a['days']);

        return $rows;
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'total' => array_sum(array_column($rows, 'total')),
                'paid' => array_sum(array_column($rows, 'paid')),
                'outstanding' => array_sum(array_column($rows, 'outstanding')),
            ],
        ];
    }

    public function getStatCards(): array
    {
        $rows = $this->getReportRows();
        $overdue = array_sum(array_column(array_values(array_filter($rows, fn ($r) => $r['status'] === 'Overdue')), 'outstanding'));

        return [
            [
                'label' => 'Open Invoices', 'value' => count($rows),
                'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
                'color' => 'blue',
            ],
            [
                'label' => 'Outstanding', 'value' => number_format(array_sum(array_column($rows, 'outstanding')), 0),
                'icon' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
                'color' => 'red',
            ],
            [
                'label' => 'Overdue Amount', 'value' => number_format($overdue, 0),
                'icon' => '<path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="10"/>',
                'color' => 'amber',
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Outstanding Credit', 'url' => '/tenant/credit-outstanding-report'],
            ['label' => 'Customer Balances', 'url' => '/tenant/credit-balance-report'],
            ['label' => 'Credit Payments', 'url' => '/tenant/credit-payments-report'],
            ['label' => 'Aging', 'url' => '/tenant/credit-aging-report'],
        ];
    }
}