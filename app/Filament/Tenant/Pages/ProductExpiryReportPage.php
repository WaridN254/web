<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProductExpiryReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';


    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory_report');
    }


    protected static ?string $title = 'Product Expiry';

    protected static ?string $slug = 'product-expiry-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Product expiry date report';
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
            ['key' => 'serial_no', 'label' => 'Serial No', 'type' => 'text'],
            ['key' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
            ['key' => 'manufactured_date', 'label' => 'Manufactured Date', 'type' => 'date'],
            ['key' => 'expired_date', 'label' => 'Expired Date', 'type' => 'date'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $query = DB::table('products as p')
            ->where('p.tenant_id', $tenantId)
            ->where('p.is_deleted', false)
            ->whereNotNull('p.expiration_date')
            ->when($from, fn ($q) => $q->where('p.expiration_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('p.expiration_date', '<=', $to . ' 23:59:59'))
            ->selectRaw("
                COALESCE(p.barcode, '') AS sku,
                '—' AS serial_no,
                p.name AS product_name,
                p.created_at AS manufactured_date,
                p.expiration_date AS expired_date
            ")
            ->orderBy('p.expiration_date');

        return array_map(fn ($r) => (array) $r, $query->get()->all());
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