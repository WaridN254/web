<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLedger extends Page
{
    use HasPermission;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.customers');
    }


    protected static ?int $navigationSort = 1;


    public static function getNavigationLabel(): string
    {
        return __('navigation.customers');
    }


    protected static ?string $slug = 'customer-ledger';

    protected string $view = 'filament.tenant.pages.customer-ledger';

    public ?string $selectedCustomerId = null;

    public string $ledgerTab = 'credit';

    public bool $paymentModalOpen = false;

    public ?string $paymentInvoiceId = null;

    public ?string $paymentAmount = null;

    public ?string $paymentMethodId = null;

    public bool $showPaymentReceipt = false;

    public bool $showSendEmailModal = false;
    public string $sendEmailDocType = 'statement';
    public string $sendEmailDocId = '';
    public string $sendEmailTo = '';
    public string $sendEmailSubject = '';
    public string $sendEmailMessage = '';
    public bool $sendEmailSending = false;
    public string $sendEmailResult = '';
    public bool $sendEmailSuccess = false;

    public int $renderKey = 0;

    public ?array $paymentReceipt = null;

    public function mount(?string $customer = null): void
    {
        $requested = $customer ?? request('customer');

        $this->selectedCustomerId = $requested && Customer::query()->whereKey($requested)->exists()
            ? $requested
            : Customer::query()->orderBy('full_name')->value('id');

        $tab = request('tab', 'credit');
        $this->ledgerTab = in_array($tab, ['credit', 'layaway'], true) ? $tab : 'credit';

        $invoices = $this->openInvoices();

        if (! empty($invoices)) {
            $this->paymentInvoiceId = $invoices[0]['id'];
            $this->paymentAmount = (string) $invoices[0]['balance_due'];
        }

        $methods = $this->paymentMethods();
        $this->paymentMethodId = ! empty($methods) ? $methods[0]->id : null;
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function customers()
    {
        return Customer::query()->orderBy('full_name')->get();
    }

    public function selectedCustomer(): ?Customer
    {
        if (! $this->selectedCustomerId) {
            return null;
        }

        return Customer::query()->find($this->selectedCustomerId);
    }

    public function creditEntries(): array
    {
        return $this->entries(['credit', 'credit_payment']);
    }

    public function layawayEntries(): array
    {
        return $this->entries(['layaway', 'layaway_payment']);
    }

    private function entries(array $types): array
    {
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return [];
        }

        $rows = DB::table('customer_ledger')
            ->where('customer_id', $customer->getKey())
            ->whereIn('type', $types)
            ->orderByDesc('date')
            ->get()
            ->all();

        return $this->withRunningBalance($rows);
    }

    private function withRunningBalance(array $rows): array
    {
        $balance = 0.0;
        $result = [];

        foreach (array_reverse($rows) as $row) {
            $balance += (float) $row->amount;
            $result[] = [
                'entry' => $row,
                'running' => $balance,
            ];
        }

        return array_reverse($result);
    }

    public function layawayPlans(): array
    {
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return [];
        }

        return DB::table('layaway_plans')
            ->where('customer_id', $customer->getKey())
            ->where('is_collected', false)
            ->orderBy('due_date')
            ->get()
            ->all();
    }

    public function creditSummary(): array
    {
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return ['total' => 0, 'paid' => 0, 'outstanding' => 0];
        }

        $rows = DB::table('customer_ledger')
            ->where('customer_id', $customer->getKey())
            ->whereIn('type', ['credit', 'credit_payment'])
            ->get();

        $total = (float) $rows->where('type', 'credit')->sum('amount');
        $paid = (float) $rows->where('type', 'credit_payment')->sum('amount');

        return [
            'total' => $total,
            'paid' => abs($paid),
            'outstanding' => max(0, $total + $paid),
        ];
    }

    public function layawaySummary(): array
    {
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return ['total' => 0, 'paid' => 0, 'outstanding' => 0, 'plans' => 0];
        }

        $rows = DB::table('customer_ledger')
            ->where('customer_id', $customer->getKey())
            ->whereIn('type', ['layaway', 'layaway_payment'])
            ->get();

        $total = (float) $rows->where('type', 'layaway')->sum('amount');
        $paid = (float) $rows->where('type', 'layaway_payment')->sum('amount');

        return [
            'total' => $total,
            'paid' => abs($paid),
            'outstanding' => max(0, $total + $paid),
            'plans' => DB::table('layaway_plans')
                ->where('customer_id', $customer->getKey())
                ->where('is_collected', false)
                ->count(),
        ];
    }

    public function currency(): string
    {
        return auth()->user()?->tenant?->currency_code
            ?? auth()->user()?->tenant?->settings['currency']
            ?? 'UGX';
    }

    public function canSettlePayments(): bool
    {
        return (bool) (auth()->user()?->hasPermission('can_settle_credit_payments') ?? false);
    }

    public function openInvoices(): array
    {
        if (! $this->selectedCustomerId) {
            return [];
        }

        return Transaction::query()
            ->where('customer_id', $this->selectedCustomerId)
            ->where('settlement_status', 'partial')
            ->where('balance_due', '>', 0)
            ->orderByDesc('transaction_date')
            ->get()
            ->map(fn (Transaction $tx) => [
                'id' => $tx->id,
                'receipt_number' => $tx->receipt_number,
                'transaction_date' => $tx->transaction_date,
                'due_date' => $tx->due_date,
                'total_amount' => (float) $tx->total_amount,
                'amount_paid' => (float) $tx->amount_paid,
                'balance_due' => (float) $tx->balance_due,
                'transaction_type' => $tx->transaction_type ?? (DB::table('layaway_plans')->where('transaction_id', $tx->id)->exists() ? 'layaway' : 'credit'),
            ])
            ->all();
    }

    public function paymentMethods(): array
    {
        return DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    public function openPaymentModal(): void
    {
        if (! $this->canSettlePayments()) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Permission required',
                'description' => 'You need the Credit Payments permission to record payments.',
            ]);

            return;
        }

        $invoices = $this->openInvoices();

        if (empty($invoices)) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'warning',
                'title' => 'No open invoices',
                'description' => 'This customer has no outstanding balance to collect.',
            ]);

            return;
        }

        $this->paymentInvoiceId = $invoices[0]['id'];
        $this->paymentAmount = (string) $invoices[0]['balance_due'];
        $methods = $this->paymentMethods();
        $this->paymentMethodId = ! empty($methods) ? $methods[0]->id : null;
        $this->paymentModalOpen = true;
    }

    public function paymentInvoiceChanged(): void
    {
        foreach ($this->openInvoices() as $invoice) {
            if ($invoice['id'] === $this->paymentInvoiceId) {
                $this->paymentAmount = (string) $invoice['balance_due'];

                break;
            }
        }
    }

    public function selectedInvoice(): ?array
    {
        foreach ($this->openInvoices() as $invoice) {
            if ($invoice['id'] === $this->paymentInvoiceId) {
                $invoice['items'] = $this->invoiceItems($invoice['id']);

                return $invoice;
            }
        }

        return null;
    }

    public function invoiceItems(string $transactionId): array
    {
        return DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->where(function ($query) {
                $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['product_name', 'quantity', 'unit_price', 'line_total', 'sale_unit_label'])
            ->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'unit_label' => $item->sale_unit_label,
            ])
            ->all();
    }

    public function recordPayment($invoiceId = null, $amount = null, $methodId = null): void
    {
        if ($invoiceId !== null && $invoiceId !== '') {
            $this->paymentInvoiceId = $invoiceId;
        }

        if ($amount !== null && $amount !== '') {
            $this->paymentAmount = $amount;
        }

        if ($methodId !== null && $methodId !== '') {
            $this->paymentMethodId = $methodId;
        }

        if (! $this->canSettlePayments()) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Permission required',
                'description' => 'You need the Credit Payments permission to record payments.',
            ]);

            return;
        }

        if (! $this->selectedCustomerId || ! $this->paymentInvoiceId) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'warning',
                'title' => 'Select an invoice',
                'description' => 'Choose an open invoice to receive payment against.',
            ]);

            return;
        }

        $order = Transaction::query()
            ->where('id', $this->paymentInvoiceId)
            ->where('customer_id', $this->selectedCustomerId)
            ->where('settlement_status', 'partial')
            ->first();

        if (! $order) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Invoice not found',
            ]);

            return;
        }

        $due = max(0, (float) $order->total_amount - (float) $order->amount_paid);
        $amount = (float) ($this->paymentAmount ?? 0);

        if ($amount <= 0 || $amount > $due) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'warning',
                'title' => 'Invalid amount',
                'description' => 'Enter an amount between 1 and ' . number_format($due, 0) . '.',
            ]);

            return;
        }

        $customer = Customer::query()->find($this->selectedCustomerId);
        $method = $this->paymentMethodId
            ? PaymentMethod::query()->find($this->paymentMethodId)
            : PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first();

        $reference = 'PAY-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        $receipt = null;

        try {
            DB::transaction(function () use ($order, $amount, $due, $method, $reference, $customer, &$receipt) {
                $newPaid = (float) $order->amount_paid + $amount;
                $newDue = max(0, (float) $order->total_amount - $newPaid);
                $fullyPaid = $newDue <= 0;

                $order->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newDue,
                    'change_given' => 0,
                    'status' => $fullyPaid ? 'completed' : 'partial',
                    'settlement_status' => $fullyPaid ? 'paid' : 'partial',
                ]);

                $paymentPayload = [
                    'id' => (string) Str::uuid(),
                    'transaction_id' => $order->id,
                    'payment_method_id' => $method?->id,
                    'payment_method' => $method?->name ?? 'Cash',
                    'amount_paid' => $amount,
                    'reference_number' => $reference,
                    'payment_date' => now(),
                    'session_id' => $order->session_id,
                    'tenant_id' => $order->tenant_id,
                    'is_deleted' => false,
                    'sort_order' => 0,
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                ];

                if (DB::getSchemaBuilder()->hasColumn('transaction_payments', 'created_at')) {
                    $paymentPayload['created_at'] = now();
                }

                if (DB::getSchemaBuilder()->hasColumn('transaction_payments', 'updated_at')) {
                    $paymentPayload['updated_at'] = now();
                }

                DB::table('transaction_payments')->insert($paymentPayload);

                $isLayaway = DB::table('layaway_plans')->where('transaction_id', $order->id)->exists();

                $this->writeLedgerEntry(
                    $customer->getKey(),
                    $order->id,
                    $isLayaway ? 'layaway_payment' : 'credit_payment',
                    -$amount,
                    'Payment ' . $reference . ' for ' . $order->receipt_number,
                    $order->tenant_id
                );

                if ($isLayaway) {
                    DB::table('layaway_plans')
                        ->where('transaction_id', $order->id)
                        ->update([
                            'amount_paid' => $newPaid,
                            'status' => $fullyPaid ? 'completed' : 'active',
                            'is_collected' => $fullyPaid,
                            'updated_at' => now(),
                        ]);
                }

                $receipt = [
                    'reference' => $reference,
                    'customer' => $customer?->full_name ?? 'Customer',
                    'invoice' => $order->receipt_number,
                    'invoice_date' => $order->transaction_date,
                    'method' => $method?->name ?? 'Cash',
                    'amount' => $amount,
                    'remaining' => $newDue,
                    'total' => (float) $order->total_amount,
                    'paid_total' => $newPaid,
                    'paid_at' => now(),
                    'type' => $isLayaway ? 'Layaway' : 'Credit',
                    'items' => $this->invoiceItems($order->id),
                ];
            });
        } catch (\Throwable $exception) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Payment failed',
                'description' => $exception->getMessage(),
            ]);

            return;
        }

        $this->paymentModalOpen = false;
        $this->paymentReceipt = $receipt;
        $this->showPaymentReceipt = true;
        $this->renderKey++;

        $this$this->dispatch('gooey-toast', [
            'type' => 'success',
            'title' => 'Payment recorded',
            'description' => $reference . ' — ' . number_format($amount, 0) . ' received.',
        ]);
    }

    public function closePaymentReceipt(): void
    {
        $this->showPaymentReceipt = false;
        $this->paymentReceipt = null;
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

    private function writeLedgerEntry(string $customerId, ?string $transactionId, string $type, float $amount, string $description, ?string $tenantId = null): void
    {
        $tenantId ??= auth()->user()?->tenant_id;
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
        $date = now();

        DB::transaction(function () use ($customerId, $transactionId, $type, $amount, $description, $tenantId, $branchId, $date) {
            $lastEntry = DB::table('customer_ledger')
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $balanceAfter = $lastEntry ? (float) $lastEntry->balance_after + $amount : $amount;

            DB::table('customer_ledger')->insert([
                'id' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'transaction_id' => $transactionId,
                'type' => $type,
                'description' => $description,
                'amount' => round($amount, 2),
                'balance_after' => round($balanceAfter, 2),
                'date' => $date,
                'created_at' => $date,
                'sync_status' => 'pending',
                'last_synced_at' => null,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]);

            DB::table('customers')
                ->where('id', $customerId)
                ->update(['balance' => round($balanceAfter, 2), 'updated_at' => $date]);
        });
    }

    public function companyName(): string
    {
        $tenant = auth()->user()?->tenant;

        return $tenant?->business?->name ?? $tenant?->name ?? 'HALIS';
    }
}