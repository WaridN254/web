<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SoldStockPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';


    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory_report');
    }


    protected static ?string $title = 'Sold Stock';

    protected static ?string $slug = 'sold-stock';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Product wise sold stock report';
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
            ['key' => 'sku', 'label' => 'SKU', 'type' => 'text'],
            ['key' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
            ['key' => 'unit', 'label' => 'Unit', 'type' => 'text'],
            ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'number', 'align' => 'right'],
            ['key' => 'tax_value', 'label' => 'Tax Value', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $query = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->leftJoin('products as p', 'ti.product_id', '=', 'p.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('t.status', 'completed')
            ->whereNotIn('t.document_type', ['refund', 'return'])
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->groupBy('ti.product_id', 'p.name', 'p.barcode', 'p.unit_of_measure', 'ti.product_name')
            ->selectRaw("
                ti.product_id,
                COALESCE(p.barcode, '') AS sku,
                COALESCE(p.name, ti.product_name) AS product_name,
                COALESCE(NULLIF(p.unit_of_measure, ''), 'Pcs') AS unit,
                COALESCE(SUM(ti.quantity), 0) AS quantity,
                COALESCE(SUM(ti.tax_amount), 0) AS tax_value,
                COALESCE(SUM(ti.line_total), 0) AS total
            ")
            ->orderByDesc('quantity');

        return array_map(fn ($r) => (array) $r, $query->get()->all());
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'quantity' => array_sum(array_column($rows, 'quantity')),
                'tax_value' => array_sum(array_column($rows, 'tax_value')),
                'total' => array_sum(array_column($rows, 'total')),
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Inventory Report', 'url' => '/tenant/inventory-report'],
            ['label' => 'Stock History', 'url' => '/tenant/stock-history'],
            ['label' => 'Sold Stock', 'url' => '/tenant/sold-stock'],
        ];
    }
}