<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Tenant;
use App\Models\Transaction;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class InvoiceDetailsPage extends Page
{
    use HasPermission;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';


    public static function getNavigationLabel(): string
    {
        return __('navigation.invoices');
    }


    protected static ?string $title = 'Invoice Details';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.sales');
    }


    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.invoice-details-page';

    protected static ?string $slug = 'invoice-details';

    public static function getRoutePath(\Filament\Panel $panel): string
    {
        return '/invoice-details/{record}';
    }

    public ?Transaction $invoice = null;

    // Send email modal state
    public bool $showSendEmailModal = false;
    public string $sendEmailDocType = 'invoice';
    public string $sendEmailDocId = '';
    public string $sendEmailTo = '';
    public string $sendEmailSubject = '';
    public string $sendEmailMessage = '';
    public bool $sendEmailSending = false;
    public string $sendEmailResult = '';
    public bool $sendEmailSuccess = false;

    public function mount(string $record): void
    {
        $tenantId = auth()->user()?->tenant_id;

        $this->invoice = Transaction::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $record)
            ->first();

        abort_unless($this->invoice, 404);
    }

    public function getItems(): array
    {
        return DB::table('transaction_items')
            ->where('transaction_id', $this->invoice->id)
            ->where('is_deleted', false)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->all();
    }

    public function getPayments(): array
    {
        return DB::table('transaction_payments')
            ->where('transaction_id', $this->invoice->id)
            ->where('is_deleted', false)
            ->orderByDesc('payment_date')
            ->get()
            ->all();
    }

    public function getCustomer(): ?object
    {
        return $this->invoice->customer_id
            ? DB::table('customers')->where('id', $this->invoice->customer_id)->first()
            : null;
    }

    public function getBranch(): ?object
    {
        return $this->invoice->branch_id
            ? DB::table('branches')->where('id', $this->invoice->branch_id)->first()
            : null;
    }

    public function companyInfo(): array
    {
        $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
        $business = $tenant?->business;

        return [
            'name' => $business?->name ?? $tenant?->name ?? 'HALIS',
            'address' => $business?->address,
            'phone' => $business?->phone ?? $tenant?->admin_phone,
            'email' => $tenant?->admin_email ?? $business?->email,
            'currency' => $business?->currency_code ?? $tenant?->currency_code ?? 'UGX',
        ];
    }

    public function getInvoiceUrl(): string
    {
        return '/tenant/invoice-page';
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
            'invoice' => $service->sendInvoice($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'receipt' => $service->sendReceipt($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'statement' => $service->sendStatement($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'payment_reminder' => $service->sendPaymentReminder($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            default => ['success' => false, 'message' => 'Unknown type.'],
        };

        $this->sendEmailSuccess = $result['success'];
        $this->sendEmailResult = $result['message'];
        $this->sendEmailSending = false;
    }
}