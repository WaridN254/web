<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProductReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';


    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory_report');
    }


    protected static ?string $title = 'Product Report';

    protected static ?string $slug = 'product-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Product wise sales report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'category' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'category',
                'label' => 'Category',
                'options' => ['' => 'All Categories'] + DB::table('categories')
                    ->where('tenant_id', $this->tenantId())
                    ->where('is_deleted', false)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all(),
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'sku', 'label' => 'SKU', 'type' => 'text'],
            ['key' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'brand', 'label' => 'Brand', 'type' => 'text'],
            ['key' => 'qty', 'label' => 'Qty', 'type' => 'number', 'align' => 'right'],
            ['key' => 'price', 'label' => 'Price', 'type' => 'money', 'align' => 'right'],
            ['key' => 'total_ordered', 'label' => 'Total Ordered', 'type' => 'number', 'align' => 'right'],
            ['key' => 'revenue', 'label' => 'Revenue', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $category = $this->filterValue('category');

        $branchId = $this->activeBranchId();

        $soldSub = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('t.status', 'completed')
            ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) AS total_ordered, COALESCE(SUM(line_total), 0) AS revenue')
            ->groupBy('product_id');

        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoinSub($soldSub, 'sa', function ($join) {
                $join->on('p.id', '=', 'sa.product_id');
            })
            ->where('p.tenant_id', $tenantId)
            ->where('p.is_deleted', false)
            ->when($category, fn ($q) => $q->where('p.category_id', $category))
            ->selectRaw("
                COALESCE(p.barcode, '') AS sku,
                p.name AS product_name,
                COALESCE(c.name, '') AS category,
                COALESCE(b.name, '') AS brand,
                COALESCE(p.current_stock, 0) AS qty,
                COALESCE(p.billing_price, 0) AS price,
                COALESCE(sa.total_ordered, 0) AS total_ordered,
                COALESCE(sa.revenue, 0) AS revenue
            ")
            ->orderBy('p.name');

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
                'total_ordered' => array_sum(array_column($rows, 'total_ordered')),
                'revenue' => array_sum(array_column($rows, 'revenue')),
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Product Report', 'url' => '/tenant/product-report'],
            ['label' => 'Product Expiry', 'url' => '/tenant/product-expiry-report'],
            ['label' => 'Product Quantity Alert', 'url' => '/tenant/product-quantity-alert'],
        ];
    }
}