<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SalesReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';


    public static function getNavigationLabel(): string
    {
        return __('navigation.sales_report');
    }


    protected static ?string $title = 'Sales Report';

    protected static ?string $slug = 'sales-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Product wise sales report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
        ]);
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

        $this->scopeQueryToBranch($query, 't.branch_id');

        return array_map(fn ($r) => (array) $r, $query->get()->all());
    }

    protected function getRows(): array
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        return $this->cachedRows = $this->getReportRows();
    }

    public function getStatCards(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $q = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->whereNotIn('document_type', ['refund', 'return'])
            ->when($from, fn ($query) => $query->where('transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($query) => $query->where('transaction_date', '<=', $to . ' 23:59:59'));

        $this->scopeQueryToBranch($q, 'branch_id');

        $total = (float) (clone $q)->sum('total_amount');
        $paid = (float) (clone $q)->sum('amount_paid');

        $unpaidQ = (clone $q)->where('balance_due', '>', 0);
        $unpaid = (float) (clone $unpaidQ)->sum('balance_due');

        $overdue = (float) (clone $unpaidQ)
            ->where('transaction_date', '<', now()->subDays(15)->format('Y-m-d') . ' 23:59:59')
            ->sum('balance_due');

        $fmt = fn ($v) => number_format($v, 2);

        return [
            ['label' => 'Total Amount', 'value' => $fmt($total), 'color' => 'blue', 'icon' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>'],
            ['label' => 'Total Paid', 'value' => $fmt($paid), 'color' => 'green', 'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
            ['label' => 'Total Unpaid', 'value' => $fmt($unpaid), 'color' => 'amber', 'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
            ['label' => 'Overdue', 'value' => $fmt($overdue), 'color' => 'red', 'icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>'],
        ];
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