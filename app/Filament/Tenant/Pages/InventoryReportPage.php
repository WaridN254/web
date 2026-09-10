<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class InventoryReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';


    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory_report');
    }


    protected static ?string $title = 'Inventory Report';

    protected static ?string $slug = 'inventory-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Current stock report';
    }

    protected function defaultFilters(): array
    {
        return ['category' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
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
            ['key' => 'unit', 'label' => 'Unit', 'type' => 'text'],
            ['key' => 'instock', 'label' => 'InStock', 'type' => 'number', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $category = $this->filterValue('category');

        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('p.tenant_id', $tenantId)
            ->where('p.is_deleted', false)
            ->when($category, fn ($q) => $q->where('p.category_id', $category))
            ->selectRaw("
                COALESCE(p.barcode, '') AS sku,
                p.name AS product_name,
                COALESCE(c.name, '') AS category,
                COALESCE(NULLIF(p.unit_of_measure, ''), 'Pcs') AS unit,
                COALESCE(p.current_stock, 0) AS instock
            ")
            ->orderBy('p.name');

        $this->scopeQueryToBranch($query, 'p.branch_id');

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
                'instock' => array_sum(array_column($rows, 'instock')),
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