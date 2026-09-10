<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreditAgingReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';


    public static function getNavigationLabel(): string
    {
        return __('navigation.credit_aging');
    }


    protected static ?string $title = 'Credit Aging';

    protected static ?string $slug = 'credit-aging-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 24;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Outstanding receivables bucketed by how overdue they are';
    }

    protected function defaultFilters(): array
    {
        return ['customer' => ''];
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
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'invoice', 'label' => 'Invoice', 'type' => 'text'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Sale Date', 'type' => 'date'],
            ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money', 'align' => 'right'],
            ['key' => 'days', 'label' => 'Days Due', 'type' => 'number', 'align' => 'right'],
            [
                'key' => 'bucket', 'label' => 'Bucket', 'type' => 'badge', 'dot' => true,
                'colors' => ['Current' => 'green', '1-30 days' => 'cyan', '31-60 days' => 'blue', '61-90 days' => 'amber', '90+ days' => 'red', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $customer = $this->filterValue('customer');

        $query = DB::table('transactions as t')
            ->leftJoin('customers as c', 't.customer_id', '=', 'c.id')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->where('t.settlement_status', 'partial')
            ->where('t.balance_due', '>', 0)
            ->whereNotNull('t.customer_id')
            ->when($customer, fn ($q) => $q->where('t.customer_id', $customer))
            ->selectRaw("
                t.receipt_number AS invoice,
                COALESCE(c.full_name, t.customer_name, 'Customer') AS customer,
                t.transaction_date AS date,
                t.due_date AS due_date,
                t.balance_due AS outstanding,
                EXTRACT(EPOCH FROM (CURRENT_DATE - COALESCE(t.due_date, t.transaction_date))) / 86400 AS days
            ");

        $this->scopeQueryToBranch($query, 't.branch_id');

        $rows = $query->get()
            ->map(function ($r) {
                $r = (array) $r;
                $days = max(0, (int) round((float) $r['days']));
                $r['days'] = $days;

                $bucket = 'Current';
                if ($days > 0 && $days <= 30) {
                    $bucket = '1-30 days';
                } elseif ($days > 30 && $days <= 60) {
                    $bucket = '31-60 days';
                } elseif ($days > 60 && $days <= 90) {
                    $bucket = '61-90 days';
                } elseif ($days > 90) {
                    $bucket = '90+ days';
                }
                $r['bucket'] = $bucket;

                return $r;
            })
            ->all();

        usort($rows, fn ($a, $b) => $b['days'] <=> $a['days']);

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
                'outstanding' => array_sum(array_column($rows, 'outstanding')),
            ],
        ];
    }

    public function getStatCards(): array
    {
        $rows = $this->getRows();
        $sum = fn (string $bucket) => array_sum(array_column(array_values(array_filter($rows, fn ($r) => $r['bucket'] === $bucket)), 'outstanding'));

        return [
            [
                'label' => 'Current', 'value' => number_format($sum('Current'), 0),
                'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
                'color' => 'green',
            ],
            [
                'label' => '1-30 Days', 'value' => number_format($sum('1-30 days'), 0),
                'icon' => '<path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="10"/>',
                'color' => 'cyan',
            ],
            [
                'label' => '31-60 Days', 'value' => number_format($sum('31-60 days'), 0),
                'icon' => '<path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="10"/>',
                'color' => 'blue',
            ],
            [
                'label' => '61-90 Days', 'value' => number_format($sum('61-90 days'), 0),
                'icon' => '<path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="10"/>',
                'color' => 'amber',
            ],
            [
                'label' => '90+ Days', 'value' => number_format($sum('90+ days'), 0),
                'icon' => '<path d="m13 2-2 5h3l-2 5"/><circle cx="12" cy="12" r="10"/>',
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