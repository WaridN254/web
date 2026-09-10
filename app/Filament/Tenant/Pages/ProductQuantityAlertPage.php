<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProductQuantityAlertPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';


    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory_report');
    }


    protected static ?string $title = 'Product Quantity Alert';

    protected static ?string $slug = 'product-quantity-alert';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Product quantity alert report';
    }

    protected function defaultFilters(): array
    {
        return ['category' => ''];
    }

    public function getFilterFields(): array
    {
        return [
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
        ];
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'sku', 'label' => 'SKU', 'type' => 'text'],
            ['key' => 'serial_no', 'label' => 'Serial No', 'type' => 'text'],
            ['key' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
            ['key' => 'total_qty', 'label' => 'Total Quantity', 'type' => 'number', 'align' => 'right'],
            ['key' => 'alert_qty', 'label' => 'Alert Quantity', 'type' => 'number', 'align' => 'right'],
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
            ->where('p.reorder_level', '>', 0)
            ->whereColumn('p.current_stock', '<=', 'p.reorder_level')
            ->when($category, fn ($q) => $q->where('p.category_id', $category))
            ->selectRaw("
                COALESCE(p.barcode, '') AS sku,
                '—' AS serial_no,
                p.name AS product_name,
                COALESCE(p.current_stock, 0) AS total_qty,
                p.reorder_level AS alert_qty
            ")
            ->orderBy('p.current_stock');

        return array_map(fn ($r) => (array) $r, $query->get()->all());
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'total_qty' => array_sum(array_column($rows, 'total_qty')),
                'alert_qty' => array_sum(array_column($rows, 'alert_qty')),
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