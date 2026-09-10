<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SupplierDueReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';


    public static function getNavigationLabel(): string
    {
        return __('navigation.supplier_report');
    }


    protected static ?string $title = 'Supplier Due';

    protected static ?string $slug = 'supplier-due-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Supplier due report';
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
            ['key' => 'amount', 'label' => 'Total Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Paid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'due', 'label' => 'Due', 'type' => 'money', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Paid' => 'green', 'Pending' => 'red', 'Partial' => 'amber', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $supplier = $this->filterValue('supplier');

        $query = DB::table('purchase_orders as po')
            ->join('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->where('po.tenant_id', $tenantId)
            ->when($from, fn ($q) => $q->where('po.purchase_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('po.purchase_date', '<=', $to . ' 23:59:59'))
            ->when($supplier, fn ($q) => $q->where('po.supplier_id', $supplier))
            ->selectRaw("
                po.purchase_number AS reference,
                UPPER(LEFT(s.id, 8)) AS id,
                s.company_name AS supplier,
                COALESCE(po.grand_total, 0) AS amount,
                COALESCE(po.paid_amount, 0) AS paid,
                COALESCE(po.due_amount, po.grand_total) AS due,
                INITCAP(po.payment_status) AS status
            ")
            ->orderByDesc('po.purchase_date');

        $this->scopeQueryToBranch($query, 'po.branch_id');

        $rows = [];
        foreach ($query->get() as $r) {
            $row = (array) $r;
            if ($row['status'] === 'Paid') {
                $row['status'] = 'Paid';
            } elseif ((float) $row['due'] > 0 && (float) $row['paid'] > 0) {
                $row['status'] = 'Partial';
            } elseif ((float) $row['due'] > 0) {
                $row['status'] = 'Pending';
            }
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
                'amount' => array_sum(array_column($rows, 'amount')),
                'paid' => array_sum(array_column($rows, 'paid')),
                'due' => array_sum(array_column($rows, 'due')),
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