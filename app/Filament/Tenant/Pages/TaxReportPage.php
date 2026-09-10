<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TaxReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';


    public static function getNavigationLabel(): string
    {
        return __('navigation.tax_report');
    }


    protected static ?string $title = 'Tax Report';

    protected static ?string $slug = 'tax-reports';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 17;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public string $tab = 'sales';

    public function mount(): void
    {
        $this->filters = $this->defaultFilters();
        $this->tab = request()->query('tab', 'sales') === 'purchase' ? 'purchase' : 'sales';
    }

    public static function getSubtitle(): string
    {
        return 'Tax wise report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'supplier', 'label' => 'Supplier', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'store', 'label' => 'Store', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'text'],
            ['key' => 'discount', 'label' => 'Discount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'tax_amount', 'label' => 'Tax Amount', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        return $this->tab === 'purchase'
            ? $this->purchaseTaxRows()
            : $this->salesTaxRows();
    }

    private function salesTaxRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $query = DB::table('transactions as t')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->selectRaw("
                t.receipt_number AS reference,
                COALESCE(t.customer_name, 'Walk-in Customer') AS supplier,
                t.transaction_date AS date,
                COALESCE(t.total_amount, 0) AS amount,
                COALESCE(t.payment_method, '') AS payment_method,
                COALESCE(t.discount_amount, 0) AS discount,
                COALESCE(t.tax_amount, 0) AS tax_amount
            ")
            ->orderByDesc('t.transaction_date');

        $this->scopeQueryToBranch($query, 't.branch_id');

        return $this->decorateRows($query->get()->all());
    }

    private function purchaseTaxRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $latestPayment = DB::raw('(SELECT DISTINCT ON (purchase_id) purchase_id, payment_method FROM purchase_payments ORDER BY purchase_id, payment_date DESC) AS lpm');

        $query = DB::table('purchase_orders as po')
            ->join('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin($latestPayment, 'po.id', '=', 'lpm.purchase_id')
            ->where('po.tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('po.purchase_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('po.purchase_date', '<=', $to . ' 23:59:59'))
            ->selectRaw("
                po.purchase_number AS reference,
                s.company_name AS supplier,
                po.purchase_date AS date,
                COALESCE(po.grand_total, 0) AS amount,
                COALESCE(lpm.payment_method, '') AS payment_method,
                COALESCE(po.discount, 0) AS discount,
                COALESCE(po.tax, 0) AS tax_amount
            ")
            ->orderByDesc('po.purchase_date');

        $this->scopeQueryToBranch($query, 'po.branch_id');

        return $this->decorateRows($query->get()->all());
    }

    private function decorateRows(array $rawRows): array
    {
        $store = $this->companyName();
        $rows = [];
        foreach ($rawRows as $r) {
            $row = (array) $r;
            $row['store'] = $store;
            $row['payment_method'] = $row['payment_method'] !== '' ? ucwords($row['payment_method']) : '—';
            $rows[] = $row;
        }

        return $rows;
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'amount' => array_sum(array_column($rows, 'amount')),
                'discount' => array_sum(array_column($rows, 'discount')),
                'tax_amount' => array_sum(array_column($rows, 'tax_amount')),
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Sales Tax', 'url' => '/tenant/tax-reports?tab=sales'],
            ['label' => 'Purchase Tax', 'url' => '/tenant/tax-reports?tab=purchase'],
        ];
    }
}