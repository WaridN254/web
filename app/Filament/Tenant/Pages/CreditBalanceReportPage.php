<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreditBalanceReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';


    public static function getNavigationLabel(): string
    {
        return __('navigation.reports');
    }


    protected static ?string $title = 'Customer Balances';

    protected static ?string $slug = 'credit-balance-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 22;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Outstanding balance, credit limit and available credit per customer';
    }

    protected function defaultFilters(): array
    {
        return ['customer' => '', 'limit' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
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
            [
                'type' => 'select',
                'key' => 'limit',
                'label' => 'Status',
                'options' => ['' => 'All Balances', 'over_limit' => 'Over Credit Limit', 'due' => 'Has Outstanding Balance'],
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'reference', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'money', 'align' => 'right'],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money', 'align' => 'right'],
            ['key' => 'available', 'label' => 'Available', 'type' => 'money', 'align' => 'right'],
            ['key' => 'overdue', 'label' => 'Overdue', 'type' => 'money', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Over limit' => 'red', 'At limit' => 'amber', 'Ok' => 'green', 'No balance' => 'secondary', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $customer = $this->filterValue('customer');
        $limit = $this->filterValue('limit');

        $branchId = $this->activeBranchId();

        $overdueSub = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->where('settlement_status', 'partial')
            ->where('balance_due', '>', 0)
            ->whereRaw('COALESCE(due_date, transaction_date) < CURRENT_DATE')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('customer_id, COALESCE(SUM(balance_due), 0) AS overdue')
            ->groupBy('customer_id');

        $rows = DB::table('customers as c')
            ->leftJoinSub($overdueSub, 'oa', function ($join) {
                $join->on('c.id', '=', 'oa.customer_id');
            })
            ->where('c.tenant_id', $tenantId)
            ->where('c.is_deleted', false)
            ->when($customer, fn ($q) => $q->where('c.id', $customer))
            ->where(function ($q) {
                $q->where('c.balance', '>', 0)
                    ->orWhere('c.credit_limit', '>', 0)
                    ->orWhere('c.credit_enabled', true);
            })
            ->selectRaw("
                UPPER(LEFT(c.id, 8)) AS reference,
                c.full_name AS customer,
                COALESCE(c.credit_limit, 0) AS credit_limit,
                COALESCE(c.balance, 0) AS outstanding,
                GREATEST(0, COALESCE(c.credit_limit, 0) - COALESCE(c.balance, 0)) AS available,
                COALESCE(oa.overdue, 0) AS overdue,
                CASE
                    WHEN COALESCE(c.balance, 0) <= 0 THEN 'No balance'
                    WHEN COALESCE(c.credit_limit, 0) > 0 AND COALESCE(c.balance, 0) >= COALESCE(c.credit_limit, 0) THEN 'Over limit'
                    WHEN COALESCE(c.credit_limit, 0) > 0 AND COALESCE(c.balance, 0) = COALESCE(c.credit_limit, 0) THEN 'At limit'
                    ELSE 'Ok'
                END AS status
            ")
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        if ($limit === 'over_limit') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['status'] === 'Over limit'));
        } elseif ($limit === 'due') {
            $rows = array_values(array_filter($rows, fn ($r) => (float) $r['outstanding'] > 0));
        }

        usort($rows, fn ($a, $b) => $b['outstanding'] <=> $a['outstanding']);

        return $rows;
    }

    public function getTotalsRow(): ?array
    {
        $rows = $this->getReportRows();

        return [
            'label' => 'Total',
            'values' => [
                'credit_limit' => array_sum(array_column($rows, 'credit_limit')),
                'outstanding' => array_sum(array_column($rows, 'outstanding')),
                'available' => array_sum(array_column($rows, 'available')),
                'overdue' => array_sum(array_column($rows, 'overdue')),
            ],
        ];
    }

    public function getStatCards(): array
    {
        $rows = $this->getReportRows();

        return [
            [
                'label' => 'Customers', 'value' => count($rows),
                'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'color' => 'blue',
            ],
            [
                'label' => 'Outstanding', 'value' => number_format(array_sum(array_column($rows, 'outstanding')), 0),
                'icon' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
                'color' => 'red',
            ],
            [
                'label' => 'Overdue', 'value' => number_format(array_sum(array_column($rows, 'overdue')), 0),
                'icon' => '<path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="10"/>',
                'color' => 'amber',
            ],
            [
                'label' => 'Over Limit', 'value' => count(array_filter($rows, fn ($r) => $r['status'] === 'Over limit')),
                'icon' => '<path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>',
                'color' => 'red',
            ],
        ];
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Outstanding Credit', 'url' => '/tenant/credit-outstanding-report'],
            ['label' => 'Customer Balances', 'url' => '/tenant/credit-balance-report'],
            ['label' => 'Credit Payments', 'url' => '/tenant/credit-payments-report'],
            ['label' => 'Aging', 'url' => '/tenant/credit-aging-report'],
        ];
    }
}