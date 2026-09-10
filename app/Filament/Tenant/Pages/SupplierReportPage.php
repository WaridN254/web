<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SupplierReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';


    public static function getNavigationLabel(): string
    {
        return __('navigation.supplier_report');
    }


    protected static ?string $title = 'Supplier Report';

    protected static ?string $slug = 'supplier-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Supplier wise purchase report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'supplier' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'supplier',
                'label' => 'Supplier',
                'options' => ['' => 'All Suppliers'] + DB::table('suppliers')
                    ->where('tenant_id', $this->tenantId())
                    ->where('is_deleted', false)
                    ->orderBy('company_name')
                    ->pluck('company_name', 'id')
                    ->all(),
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'id', 'label' => 'ID', 'type' => 'text'],
            ['key' => 'supplier', 'label' => 'Supplier', 'type' => 'text'],
            ['key' => 'total_items', 'label' => 'Total Items', 'type' => 'number', 'align' => 'right'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'text'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Received' => 'green', 'Pending' => 'amber', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $supplier = $this->filterValue('supplier');

        $latestPayment = DB::raw('(SELECT DISTINCT ON (purchase_id) purchase_id, payment_method FROM purchase_payments ORDER BY purchase_id, payment_date DESC) AS lp');
        $itemsAgg = DB::raw('(SELECT purchase_id, COALESCE(SUM(quantity), 0) AS total_items FROM purchase_items GROUP BY purchase_id) AS piagg');

        $query = DB::table('purchase_orders as po')
            ->join('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin($latestPayment, 'po.id', '=', 'lp.purchase_id')
            ->leftJoin($itemsAgg, 'po.id', '=', 'piagg.purchase_id')
            ->where('po.tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('po.purchase_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('po.purchase_date', '<=', $to . ' 23:59:59'))
            ->when($supplier, fn ($q) => $q->where('po.supplier_id', $supplier))
            ->selectRaw("
                po.purchase_number AS reference,
                UPPER(LEFT(s.id, 8)) AS id,
                s.company_name AS supplier,
                COALESCE(piagg.total_items, 0) AS total_items,
                COALESCE(po.grand_total, 0) AS amount,
                COALESCE(lp.payment_method, '') AS payment_method,
                INITCAP(po.status) AS status
            ")
            ->orderByDesc('po.purchase_date');

        $this->scopeQueryToBranch($query, 'po.branch_id');

        $rows = [];
        foreach ($query->get() as $r) {
            $row = (array) $r;
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
                'total_items' => array_sum(array_column($rows, 'total_items')),
                'amount' => array_sum(array_column($rows, 'amount')),
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Supplier Report', 'url' => '/tenant/supplier-report'],
            ['label' => 'Supplier Due', 'url' => '/tenant/supplier-due-report'],
        ];
    }
}