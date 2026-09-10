<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CustomerDueReportPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    public bool $showSendEmailModal = false;
    public string $sendEmailDocType = 'statement';
    public string $sendEmailDocId = '';
    public string $sendEmailTo = '';
    public string $sendEmailSubject = '';
    public string $sendEmailMessage = '';
    public bool $sendEmailSending = false;
    public string $sendEmailResult = '';
    public bool $sendEmailSuccess = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';


    public static function getNavigationLabel(): string
    {
        return __('navigation.customer_report');
    }


    protected static ?string $title = 'Customer Due';

    protected static ?string $slug = 'customer-due-report';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.tenant.pages.reports.layout';

    protected ?array $cachedRows = null;

    public static function getSubtitle(): string
    {
        return 'Customer due report';
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
            ['key' => 'amount', 'label' => 'Total Amount', 'type' => 'money', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Paid', 'type' => 'money', 'align' => 'right'],
            ['key' => 'due', 'label' => 'Due', 'type' => 'money', 'align' => 'right'],
            [
                'key' => 'status', 'label' => 'Status', 'type' => 'badge', 'dot' => true,
                'colors' => ['Paid' => 'green', 'Due' => 'red', 'default' => 'secondary'],
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

        $amountSub = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->selectRaw('customer_id, COALESCE(SUM(total_amount), 0) AS amount')
            ->groupBy('customer_id');

        $query = DB::table('customers as c')
            ->leftJoinSub($amountSub, 'aa', function ($join) {
                $join->on('c.id', '=', 'aa.customer_id');
            })
            ->where('c.tenant_id', $tenantId)
            ->where('c.is_deleted', false)
            ->when($customer, fn ($q) => $q->where('c.id', $customer))
            ->when($from || $to, function ($q) use ($from, $to, $branchId) {
                return $q->whereExists(function ($sub) use ($from, $to, $branchId) {
                    $sub->selectRaw('1')
                        ->from('transactions')
                        ->whereColumn('transactions.customer_id', 'c.id')
                        ->where('transactions.is_deleted', false)
                        ->when($branchId, fn ($qq) => $qq->where('transactions.branch_id', $branchId))
                        ->when($from, fn ($qq) => $qq->where('transactions.transaction_date', '>=', $from . ' 00:00:00'))
                        ->when($to, fn ($qq) => $qq->where('transactions.transaction_date', '<=', $to . ' 23:59:59'));
                });
            })
            ->where(function ($q) {
                $q->where('c.balance', '>', 0)
                    ->orWhere('c.total_spent', '>', 0);
            })
            ->selectRaw("
                UPPER(LEFT(c.id, 8)) AS reference,
                COALESCE(NULLIF(c.phone, ''), NULLIF(c.tin, ''), '—') AS code,
                c.full_name AS customer,
                COALESCE(aa.amount, c.total_spent, 0) AS amount,
                COALESCE(aa.amount, c.total_spent, 0) - COALESCE(c.balance, 0) AS paid,
                COALESCE(c.balance, 0) AS due,
                CASE WHEN COALESCE(c.balance, 0) > 0 THEN 'Due' ELSE 'Paid' END AS status
            ")
            ->orderBy('c.full_name');

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
                'amount' => array_sum(array_column($rows, 'amount')),
                'paid' => array_sum(array_column($rows, 'paid')),
                'due' => array_sum(array_column($rows, 'due')),
            ],
        ];
    }

    public function openSendEmailModal(string $docType, string $docId, string $toEmail = ''): void
    {
        $this->sendEmailDocType = $docType;
        $this->sendEmailDocId = $docId;
        $this->sendEmailTo = $toEmail;
        $this->sendEmailSubject = '';
        $this->sendEmailMessage = '';
        $this->sendEmailResult = '';
        $this->sendEmailSuccess = false;
        $this->showSendEmailModal = true;
    }

    public function closeSendEmailModal(): void
    {
        $this->showSendEmailModal = false;
    }

    public function sendEmailFromModal(): void
    {
        $this->sendEmailSending = true;
        $this->sendEmailResult = '';

        if (empty($this->sendEmailTo) || !filter_var($this->sendEmailTo, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmailResult = 'Please enter a valid email address.';
            $this->sendEmailSuccess = false;
            $this->sendEmailSending = false;
            return;
        }

        $service = new \App\Services\Email\PosEmailService();
        $tenantId = auth()->user()->tenant_id;

        $result = match ($this->sendEmailDocType) {
            'statement' => $service->sendStatement($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'payment_reminder' => $service->sendPaymentReminder($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            default => ['success' => false, 'message' => 'Unknown type.'],
        };

        $this->sendEmailSuccess = $result['success'];
        $this->sendEmailResult = $result['message'];
        $this->sendEmailSending = false;
    }

    public function getNavPills(): array
    {
        return [
            ['label' => 'Customer Report', 'url' => '/tenant/customer-report'],
            ['label' => 'Customer Due', 'url' => '/tenant/customer-due-report'],
        ];
    }
}