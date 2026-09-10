<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CustomerReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';


    public static function getNavigationLabel(): string
    {
        return __('navigation.customer_report');
    }


    protected static ?string $title = 'Customer Report';

    protected static ?string $slug = 'customer-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Customer wise sales report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'customer' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'customer',
                'label' => 'Customer',
                'options' => ['' => 'All Customers'] + DB::table('customers')
                    ->where('tenant_id', $this->tenantId())
                    ->where('is_deleted', false)
                    ->orderBy('full_name')
                    ->pluck('full_name', 'id')
                    ->all(),
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'total_orders', 'label' => 'Total Orders', 'type' => 'number', 'align' => 'right'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'text'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Active' => 'green', 'Unpaid' => 'red', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $customer = $this->filterValue('customer');

        $branchId = $this->activeBranchId();

        $ordersSub = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->selectRaw('customer_id, COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS amount')
            ->groupBy('customer_id');

        $lastPaymentSub = DB::table('transaction_payments as tp')
            ->join('transactions as t', 'tp.transaction_id', '=', 't.id')
            ->where('t.tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
            ->selectRaw('DISTINCT ON (t.customer_id) t.customer_id, tp.payment_method')
            ->orderBy('t.customer_id')
            ->orderBy('tp.payment_date', 'desc');

        $query = DB::table('customers as c')
            ->leftJoinSub($ordersSub, 'oa', function ($join) {
                $join->on('c.id', '=', 'oa.customer_id');
            })
            ->leftJoinSub($lastPaymentSub, 'lpm', function ($join) {
                $join->on('c.id', '=', 'lpm.customer_id');
            })
            ->where('c.tenant_id', $tenantId)
            ->where('c.is_deleted', false)
            ->when($customer, fn ($q) => $q->where('c.id', $customer))
            ->when($from || $to, function ($q) use ($from, $to) {
                return $q->whereExists(function ($sub) use ($from, $to) {
                    $sub->selectRaw('1')
                        ->from('transactions')
                        ->whereColumn('transactions.customer_id', 'c.id')
                        ->where('transactions.is_deleted', false)
                        ->where('transactions.status', 'completed')
                        ->when($from, fn ($qq) => $qq->where('transactions.transaction_date', '>=', $from . ' 00:00:00'))
                        ->when($to, fn ($qq) => $qq->where('transactions.transaction_date', '<=', $to . ' 23:59:59'));
                });
            })
            ->selectRaw("
                UPPER(LEFT(c.id, 8)) AS reference,
                COALESCE(NULLIF(c.phone, ''), NULLIF(c.tin, ''), '—') AS code,
                c.full_name AS customer,
                COALESCE(oa.total_orders, 0) AS total_orders,
                COALESCE(oa.amount, 0) AS amount,
                COALESCE(lpm.payment_method, '') AS payment_method,
                CASE WHEN COALESCE(c.balance, 0) > 0 THEN 'Unpaid' ELSE 'Active' END AS status
            ")
            ->orderBy('c.full_name');

        $rows = [];
        foreach ($query->get() as $r) {
            $row = (array) $r;
            $row['payment_method'] = $row['payment_method'] !== '' ? ucwords($row['payment_method']) : '—';
            $rows[] = $row;
        }

        return $rows;
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
                'total_orders' => array_sum(array_column($rows, 'total_orders')),
                'amount' => array_sum(array_column($rows, 'amount')),
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Customer Report', 'url' => '/tenant/customer-report'],
            ['label' => 'Customer Due', 'url' => '/tenant/customer-due-report'],
        ];
    }
}