<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class BestSellerPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';


    public static function getNavigationLabel(): string
    {
        return __('navigation.best_sellers');
    }


    protected static ?string $title = 'Best Seller';

    protected static ?string $slug = 'best-seller';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Best selling products report';
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
            ['key' => 'brand', 'label' => 'Brand', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'sold_qty', 'label' => 'Sold Qty', 'type' => 'number', 'align' => 'right'],
            ['key' => 'sold_amount', 'label' => 'Sold Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'instock_qty', 'label' => 'Instock Qty', 'type' => 'number', 'align' => 'right'],
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
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_brands as b', 'p.brand_id', '=', 'b.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('t.status', 'completed')
            ->whereNotIn('t.document_type', ['refund', 'return'])
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->groupBy('ti.product_id', 'p.name', 'p.barcode', 'p.current_stock', 'b.name', 'c.name', 'ti.product_name')
            ->selectRaw("
                ti.product_id,
                COALESCE(p.barcode, '') AS sku,
                COALESCE(p.name, ti.product_name) AS product_name,
                COALESCE(b.name, '') AS brand,
                COALESCE(c.name, '') AS category,
                COALESCE(SUM(ti.quantity), 0) AS sold_qty,
                COALESCE(SUM(ti.line_total), 0) AS sold_amount,
                COALESCE(p.current_stock, 0) AS instock_qty
            ")
            ->orderByDesc('sold_qty');

        return array_map(fn ($r) => (array) $r, $query->get()->all());
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
                'sold_qty' => array_sum(array_column($rows, 'sold_qty')),
                'sold_amount' => array_sum(array_column($rows, 'sold_amount')),
            ],
        ];
    }
}