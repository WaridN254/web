<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class InvoiceReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';


    public static function getNavigationLabel(): string
    {
        return __('navigation.invoices');
    }


    protected static ?string $title = 'Invoice Report';

    protected static ?string $slug = 'invoice-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Invoice wise sales report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => '', 'status' => ''];
    }

    public function getFilterFields(): array
    {
        return array_merge($this->getBranchFilterField(), [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
            [
                'type' => 'select',
                'key' => 'status',
                'label' => 'Status',
                'options' => ['' => 'All Status', 'paid' => 'Paid', 'unpaid' => 'Unpaid', 'overdue' => 'Overdue'],
            ],
        ]);
    }

    public function getReportColumns(): array
    {
        return [
            ['key' => 'invoice_no', 'label' => 'Invoice No', 'type' => 'html'],
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Paid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'amount_due', 'label' => 'Amount Due', 'type' => 'money', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Paid' => 'green', 'Unpaid' => 'red', 'Overdue' => 'amber', 'default' => 'secondary'],
            ],
        ];
    }

    public function getReportRows(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();
        $status = $this->filterValue('status');

        $query = DB::table('transactions as t')
            ->where('t.tenant_id', $tenantId)
            ->where('t.is_deleted', false)
            ->when($from, fn ($q) => $q->where('t.transaction_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('t.transaction_date', '<=', $to . ' 23:59:59'))
            ->select('t.id', 't.receipt_number', 't.customer_name', 't.transaction_date', 't.total_amount', 't.amount_paid', 't.balance_due')
            ->orderByDesc('t.transaction_date');

        $this->scopeQueryToBranch($query, 't.branch_id');

        $rows = [];
        foreach ($query->get() as $r) {
            $due = \Illuminate\Support\Carbon::parse($r->transaction_date)->addDays(15);
            if ((float) ($r->balance_due ?? 0) <= 0) {
                $statusLabel = 'Paid';
            } elseif ($due->lt(now())) {
                $statusLabel = 'Overdue';
            } else {
                $statusLabel = 'Unpaid';
            }

            if ($status === 'paid' && $statusLabel !== 'Paid') continue;
            if ($status === 'unpaid' && $statusLabel !== 'Unpaid') continue;
            if ($status === 'overdue' && $statusLabel !== 'Overdue') continue;

            $rows[] = [
                'invoice_no' => '<a href="/tenant/invoice-details/' . $r->id . '" style="color:var(--rp-orange);font-weight:600;text-decoration:none;">' . e($r->receipt_number ?? $r->id) . '</a>',
                'customer' => $r->customer_name ?? 'Walk-in Customer',
                'due_date' => $due,
                'amount' => (float) $r->total_amount,
                'paid' => (float) $r->amount_paid,
                'amount_due' => (float) ($r->balance_due ?? 0),
                'status' => $statusLabel,
            ];
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

    public function getStatCards(): array
    {
        $tenantId = $this->tenantId();
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $q = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
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
                'amount' => array_sum(array_column($rows, 'amount')),
                'paid' => array_sum(array_column($rows, 'paid')),
                'amount_due' => array_sum(array_column($rows, 'amount_due')),
            ],
        ];
    }
}