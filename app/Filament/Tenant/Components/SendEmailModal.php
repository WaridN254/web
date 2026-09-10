<?php

namespace App\Filament\Tenant\Components;

use App\Models\EmailAccount;
use App\Services\Email\PosEmailService;
use Livewire\Component;

class SendEmailModal extends Component
{
    public bool $showModal = false;
    public string $docType = 'invoice';
    public string $docId = '';
    public string $toEmail = '';
    public string $subject = '';
    public string $message = '';
    public bool $sending = false;
    public string $sendResult = '';
    public bool $sendSuccess = false;

    public function openModal(string $docType, string $docId, string $toEmail = '', string $defaultSubject = ''): void
    {
        $this->docType = $docType;
        $this->docId = $docId;
        $this->toEmail = $toEmail;
        $this->subject = $defaultSubject;
        $this->message = '';
        $this->sendResult = '';
        $this->sendSuccess = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->sending = false;
        $this->sendResult = '';
    }

    public function send(): void
    {
        $this->sending = true;
        $this->sendResult = '';

        if (empty($this->toEmail)) {
            $this->sendResult = 'Please enter an email address.';
            $this->sendSuccess = false;
            $this->sending = false;
            return;
        }

        if (!filter_var($this->toEmail, FILTER_VALIDATE_EMAIL)) {
            $this->sendResult = 'Please enter a valid email address.';
            $this->sendSuccess = false;
            $this->sending = false;
            return;
        }

        $service = new PosEmailService();
        $tenantId = auth()->user()->tenant_id;

        $result = match ($this->docType) {
            'invoice' => $service->sendInvoice($this->docId, $this->toEmail, $this->subject ?: null, $this->message ?: null, $tenantId),
            'receipt' => $service->sendReceipt($this->docId, $this->toEmail, $this->subject ?: null, $this->message ?: null, $tenantId),
            'quotation' => $service->sendQuotation($this->docId, $this->toEmail, $this->subject ?: null, $this->message ?: null, $tenantId),
            'statement' => $service->sendStatement($this->docId, $this->toEmail, $this->subject ?: null, $this->message ?: null, $tenantId),
            'payment_reminder' => $service->sendPaymentReminder($this->docId, $this->toEmail, $this->subject ?: null, $this->message ?: null, $tenantId),
            default => ['success' => false, 'message' => 'Unknown document type.'],
        };

        $this->sendSuccess = $result['success'];
        $this->sendResult = $result['message'];
        $this->sending = false;

        if ($result['success']) {
            $this->dispatch('email-sent', docType: $this->docType, docId: $this->docId);
        }
    }

    public function render()
    {
        return view('filament.tenant.components.send-email-modal');
    }
}
