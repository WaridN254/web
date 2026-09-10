<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class IncomeReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';


    public static function getNavigationLabel(): string
    {
        return __('navigation.income_report');
    }


    protected static ?string $title = 'Income Report';

    protected static ?string $slug = 'income-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 16;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'All income report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'payment_method' => ''];
    }

    public function getFilterFields(): array
    {
        $methods = DB::table('transaction_payments')
            ->where('tenant_id', $this->tenantId())
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method', 'payment_method')
            ->all();

        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'payment_method',
                'label' => 'Payment Method',
                'options' => ['' => 'All Methods'] + $methods,
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'store', 'label' => 'Store', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'notes', 'label' => 'Notes', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'text'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $method = $this->filterValue('payment_method');

        $query = DB::table('transaction_payments as tp')
            ->join('transactions as t', 'tp.transaction_id', '=', 't.id')
            ->where('tp.tenant_id', $tenantId)
            ->where('tp.is_deleted', false)
            ->where('t.is_deleted', false)
            ->when($from, fn ($q) => $q->where('tp.payment_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('tp.payment_date', '<=', $to . ' 23:59:59'))
            ->when($method, fn ($q) => $q->where('tp.payment_method', $method))
            ->selectRaw("
                t.receipt_number AS reference,
                tp.payment_date AS date,
                t.customer_name AS notes,
                COALESCE(tp.payment_method, '') AS payment_method,
                tp.amount_paid AS amount
            ")
            ->orderByDesc('tp.payment_date');

        $this->scopeQueryToBranch($query, 't.branch_id');

        $rows = [];
        $store = $this->companyName();
        foreach ($query->get() as $r) {
            $row = (array) $r;
            $row['store'] = $store;
            $row['category'] = 'Sale';
            $row['notes'] = $row['notes'] ?: 'Walk-in Customer';
            $row['payment_method'] = $row['payment_method'] !== '' ? ucwords($row['payment_method']) : '—';
            $rows[] = $row;
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