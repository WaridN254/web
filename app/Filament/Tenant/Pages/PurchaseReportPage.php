<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PurchaseReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';


    public static function getNavigationLabel(): string
    {
        return __('navigation.purchase_report');
    }


    protected static ?string $title = 'Purchase Report';

    protected static ?string $slug = 'purchase-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Product wise purchase report';
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
            ['key' => 'sku', 'label' => 'SKU', 'type' => 'text'],
            ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
            ['key' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'instock_qty', 'label' => 'Instock Qty', 'type' => 'number', 'align' => 'right'],
            ['key' => 'purchase_qty', 'label' => 'Purchase Qty', 'type' => 'number', 'align' => 'right'],
            ['key' => 'purchase_amount', 'label' => 'Purchase Amount', 'type' => 'money', 'align' => 'right'],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $supplier = $this->filterValue('supplier');

        $query = DB::table('purchase_items as pi')
            ->join('purchase_orders as po', 'pi.purchase_id', '=', 'po.id')
            ->leftJoin('products as p', 'pi.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('po.tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('po.purchase_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('po.purchase_date', '<=', $to . ' 23:59:59'))
            ->when($supplier, fn ($q) => $q->where('po.supplier_id', $supplier))
            ->selectRaw("
                po.purchase_number AS reference,
                COALESCE(p.barcode, '') AS sku,
                po.due_date,
                COALESCE(p.name, pi.product_name) AS product_name,
                COALESCE(c.name, '') AS category,
                COALESCE(p.current_stock, 0) AS instock_qty,
                pi.quantity AS purchase_qty,
                COALESCE(pi.total, 0) AS purchase_amount
            ")
            ->orderByDesc('po.purchase_date');

        $this->scopeQueryToBranch($query, 'po.branch_id');

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
                'instock_qty' => array_sum(array_column($rows, 'instock_qty')),
                'purchase_qty' => array_sum(array_column($rows, 'purchase_qty')),
                'purchase_amount' => array_sum(array_column($rows, 'purchase_amount')),
            ],
        ];
    }
}