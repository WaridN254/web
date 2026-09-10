<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class StockHistoryPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';


    public static function getNavigationLabel(): string
    {
        return __('navigation.stock_ledger');
    }


    protected static ?string $title = 'Stock History';

    protected static ?string $slug = 'stock-history';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Product wise stock movement history';
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
            ['key' => 'product_name', 'label' => 'Product', 'type' => 'text'],
            ['key' => 'initial', 'label' => 'Initial', 'type' => 'number', 'align' => 'right'],
            ['key' => 'added', 'label' => 'Added', 'type' => 'number', 'align' => 'right'],
            ['key' => 'sold', 'label' => 'Sold', 'type' => 'number', 'align' => 'right'],
            ['key' => 'defective', 'label' => 'Defective', 'type' => 'number', 'align' => 'right'],
            ['key' => 'final', 'label' => 'Final Qty', 'type' => 'number', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $query = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.id')
            ->where('sm.tenant_id', $tenantId)
            ->where('p.is_deleted', false)
            ->when($from, fn ($q) => $q->where('sm.movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('sm.movement_date', '<=', $to . ' 23:59:59'))
            ->groupBy('sm.product_id', 'p.name', 'p.barcode', 'p.current_stock')
            ->selectRaw("
                sm.product_id,
                COALESCE(p.barcode, '') AS sku,
                p.name AS product_name,
                COALESCE(p.current_stock, 0) AS final,
                COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE 0 END), 0) AS added,
                COALESCE(SUM(CASE WHEN sm.movement_type = 'out' THEN sm.quantity ELSE 0 END), 0) AS sold
            ")
            ->orderBy('p.name');

        $rows = [];
        foreach ($query->get() as $r) {
            $row = (array) $r;
            $row['defective'] = 0;
            $row['initial'] = $row['final'] - $row['added'] + $row['sold'];
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
                'initial' => array_sum(array_column($rows, 'initial')),
                'added' => array_sum(array_column($rows, 'added')),
                'sold' => array_sum(array_column($rows, 'sold')),
                'defective' => array_sum(array_column($rows, 'defective')),
                'final' => array_sum(array_column($rows, 'final')),
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