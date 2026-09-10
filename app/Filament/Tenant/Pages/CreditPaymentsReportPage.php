<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreditPaymentsReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';


    public static function getNavigationLabel(): string
    {
        return __('navigation.reports');
    }


    protected static ?string $title = 'Credit Payments';

    protected static ?string $slug = 'credit-payments-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 23;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Payments received against credit and layaway invoices';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'customer' => '', 'method' => ''];
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
                'key' => 'method',
                'label' => 'Payment Method',
                'options' => ['' => 'All Methods'] + DB::table('payment_methods')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all(),
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'invoice', 'label' => 'Invoice', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'badge', 'dot' => true, 'colors' => ['credit' => 'cyan', 'layaway' => 'purple', 'default' => 'secondary']],
            ['key' => 'method', 'label' => 'Method', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Payment Date', 'type' => 'date'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'remaining', 'label' => 'Remaining After', 'type' => 'money', 'align' => 'right'],
            ['key' => 'cashier', 'label' => 'Received By', 'type' => 'text'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $customer = $this->filterValue('customer');
        $method = $this->filterValue('method');

        $query = DB::table('transaction_payments as tp')
            ->leftJoin('transactions as t', 'tp.transaction_id', '=', 't.id')
            ->leftJoin('customers as c', 't.customer_id', '=', 'c.id')
            ->leftJoin('payment_methods as pm', 'tp.payment_method_id', '=', 'pm.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('tp.is_deleted', false)
            ->whereNotNull('t.customer_id')
            ->whereIn('t.transaction_type', ['credit', 'layaway'])
            ->when($from, fn ($q) => $q->where('tp.payment_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('tp.payment_date', '<=', $to . ' 23:59:59'))
            ->when($customer, fn ($q) => $q->where('t.customer_id', $customer))
            ->when($method, fn ($q) => $q->where('tp.payment_method_id', $method))
            ->selectRaw("
                COALESCE(NULLIF(tp.reference_number, ''), 'PAY-' || LEFT(tp.id::text, 8)) AS reference,
                COALESCE(c.full_name, t.customer_name, 'Customer') AS customer,
                t.receipt_number AS invoice,
                t.transaction_type AS type,
                COALESCE(tp.payment_method, pm.name, 'Cash') AS method,
                tp.payment_date AS date,
                tp.amount_paid AS amount,
                (t.balance_due) AS remaining,
                COALESCE(u.full_name, 'Cashier') AS cashier
            ")
            ->leftJoin('users as u', function ($join) {
                $join->whereRaw("u.id = COALESCE(NULLIF(t.cashier_id::text, ''), t.user_id)");
            })
            ->orderByDesc('tp.payment_date');

        $this->scopeQueryToBranch($query, 't.branch_id');

        return $query->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'amount' => array_sum(array_column($rows, 'amount')),
                'remaining' => array_sum(array_column($rows, 'remaining')),
            ],
        ];
    }

    public function getStatCards(): array
    {
        $rows = $this->getReportRows();
        $today = now()->format('Y-m-d');

        return [
            [
                'label' => 'Payments', 'value' => count($rows),
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
                'color' => 'green',
            ],
            [
                'label' => 'Collected', 'value' => number_format(array_sum(array_column($rows, 'amount')), 0),
                'icon' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
                'color' => 'green',
            ],
            [
                'label' => 'Today', 'value' => number_format(array_sum(array_column(array_values(array_filter($rows, fn ($r) => substr((string) $r['date'], 0, 10) === $today)), 'amount')), 0),
                'icon' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
                'color' => 'blue',
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