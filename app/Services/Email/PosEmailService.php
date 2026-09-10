<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailRecipient;
use App\Models\EmailTemplate;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PosEmailService
{
    public function getActiveAccount(string $tenantId): ?EmailAccount
    {
        return EmailAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('connection_status', 'connected')
            ->first();
    }

    public function sendInvoice(string $transactionId, string $toEmail, ?string $subject = null, ?string $message = null, string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $account = $this->getActiveAccount($tenantId);
        if (!$account) {
            return ['success' => false, 'message' => 'No connected email account.'];
        }

        $transaction = DB::table('transactions')
            ->where('id', $transactionId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->where('is_deleted', false)
            ->orderBy('sort_order')
            ->get();

        $payments = DB::table('transaction_payments')
            ->where('transaction_id', $transactionId)
            ->where('is_deleted', false)
            ->get();

        $business = DB::table('businesses')
            ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
            ->where('tenants.id', $tenantId)
            ->first();

        $customer = null;
        if ($transaction->customer_id) {
            $customer = DB::table('customers')->where('id', $transaction->customer_id)->first();
        }

        $currency = $business->currency_code ?? 'UGX';
        $companyName = $business->name ?? 'Your Business';

        $subject = $subject ?: "Invoice {$transaction->receipt_number} from {$companyName}";
        $body = $message ? "<p>" . nl2br(e($message)) . "</p><br>" : '';
        $body .= $this->renderInvoiceHtml($transaction, $items, $payments, $customer, $business, $currency);

        return $this->sendEmail($account, $toEmail, $subject, $body, $tenantId, 'invoice');
    }

    public function sendReceipt(string $transactionId, string $toEmail, ?string $subject = null, ?string $message = null, string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $account = $this->getActiveAccount($tenantId);
        if (!$account) {
            return ['success' => false, 'message' => 'No connected email account.'];
        }

        $transaction = DB::table('transactions')
            ->where('id', $transactionId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->where('is_deleted', false)
            ->orderBy('sort_order')
            ->get();

        $payments = DB::table('transaction_payments')
            ->where('transaction_id', $transactionId)
            ->where('is_deleted', false)
            ->get();

        $business = DB::table('businesses')
            ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
            ->where('tenants.id', $tenantId)
            ->first();

        $customer = null;
        if ($transaction->customer_id) {
            $customer = DB::table('customers')->where('id', $transaction->customer_id)->first();
        }

        $currency = $business->currency_code ?? 'UGX';
        $companyName = $business->name ?? 'Your Business';

        $subject = $subject ?: "Receipt {$transaction->receipt_number} from {$companyName}";
        $body = $message ? "<p>" . nl2br(e($message)) . "</p><br>" : '';
        $body .= $this->renderReceiptHtml($transaction, $items, $payments, $customer, $business, $currency);

        return $this->sendEmail($account, $toEmail, $subject, $body, $tenantId, 'receipt');
    }

    public function sendQuotation(string $quotationId, string $toEmail, ?string $subject = null, ?string $message = null, string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $account = $this->getActiveAccount($tenantId);
        if (!$account) {
            return ['success' => false, 'message' => 'No connected email account.'];
        }

        $quotation = DB::table('quotations')
            ->where('id', $quotationId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$quotation) {
            return ['success' => false, 'message' => 'Quotation not found.'];
        }

        $items = DB::table('quotation_items')
            ->where('quotation_id', $quotationId)
            ->orderBy('created_at')
            ->get();

        $business = DB::table('businesses')
            ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
            ->where('tenants.id', $tenantId)
            ->first();

        $customer = null;
        if ($quotation->customer_id) {
            $customer = DB::table('customers')->where('id', $quotation->customer_id)->first();
        }

        $currency = $business->currency_code ?? 'UGX';
        $companyName = $business->name ?? 'Your Business';

        $subject = $subject ?: "Quotation {$quotation->quote_number} from {$companyName}";
        $body = $message ? "<p>" . nl2br(e($message)) . "</p><br>" : '';
        $body .= $this->renderQuotationHtml($quotation, $items, $customer, $business, $currency);

        DB::table('quotations')->where('id', $quotationId)->update(['status' => 'sent']);

        return $this->sendEmail($account, $toEmail, $subject, $body, $tenantId, 'quotation');
    }

    public function sendStatement(string $customerId, string $toEmail, ?string $subject = null, ?string $message = null, string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $account = $this->getActiveAccount($tenantId);
        if (!$account) {
            return ['success' => false, 'message' => 'No connected email account.'];
        }

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$customer) {
            return ['success' => false, 'message' => 'Customer not found.'];
        }

        $ledger = DB::table('customer_ledger')
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->orderBy('date', 'asc')
            ->get();

        $business = DB::table('businesses')
            ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
            ->where('tenants.id', $tenantId)
            ->first();

        $currency = $business->currency_code ?? 'UGX';
        $companyName = $business->name ?? 'Your Business';

        $subject = $subject ?: "Account Statement from {$companyName}";
        $body = $message ? "<p>" . nl2br(e($message)) . "</p><br>" : '';
        $body .= $this->renderStatementHtml($customer, $ledger, $business, $currency);

        return $this->sendEmail($account, $toEmail, $subject, $body, $tenantId, 'statement');
    }

    public function sendPaymentReminder(string $transactionId, string $toEmail, ?string $subject = null, ?string $message = null, string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $account = $this->getActiveAccount($tenantId);
        if (!$account) {
            return ['success' => false, 'message' => 'No connected email account.'];
        }

        $transaction = DB::table('transactions')
            ->where('id', $transactionId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        $business = DB::table('businesses')
            ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
            ->where('tenants.id', $tenantId)
            ->first();

        $customer = null;
        if ($transaction->customer_id) {
            $customer = DB::table('customers')->where('id', $transaction->customer_id)->first();
        }

        $currency = $business->currency_code ?? 'UGX';
        $companyName = $business->name ?? 'Your Business';

        $balanceDue = (float) ($transaction->total_amount ?? 0) - (float) ($transaction->amount_paid ?? 0);

        $subject = $subject ?: "Payment Reminder - {$transaction->receipt_number} - {$companyName}";
        $body = $message ? "<p>" . nl2br(e($message)) . "</p><br>" : '';
        $body .= $this->renderPaymentReminderHtml($transaction, $customer, $business, $balanceDue, $currency);

        return $this->sendEmail($account, $toEmail, $subject, $body, $tenantId, 'payment_reminder');
    }

    private function sendEmail(EmailAccount $account, string $toEmail, string $subject, string $bodyHtml, string $tenantId, string $docType): array
    {
        $emailId = Str::uuid();
        $email = Email::create([
            'id' => $emailId,
            'tenant_id' => $tenantId,
            'email_account_id' => $account->id,
            'from_name' => $account->display_name ?? $account->email_address,
            'from_address' => $account->email_address,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => strip_tags($bodyHtml),
            'folder' => 'sent',
            'is_draft' => false,
            'is_read' => true,
            'has_attachments' => true,
            'sent_at' => now(),
        ]);

        EmailRecipient::create([
            'id' => Str::uuid(),
            'email_id' => $emailId,
            'type' => 'to',
            'email_address' => $toEmail,
        ]);

        // Attach the document as an HTML file
        $docNames = [
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'quotation' => 'Quotation',
            'statement' => 'Statement',
            'payment_reminder' => 'Payment Reminder',
        ];
        $docName = $docNames[$docType] ?? 'Document';
        $filename = Str::uuid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $docName) . '.html';
        $directory = 'emails/' . $tenantId;

        Storage::disk('local')->put($directory . '/' . $filename, $bodyHtml);

        EmailAttachment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenantId,
            'email_id' => $emailId,
            'original_name' => $docName . '.html',
            'file_name' => $filename,
            'storage_disk' => 'local',
            'storage_path' => $directory . '/' . $filename,
            'file_size' => strlen($bodyHtml),
            'mime_type' => 'text/html',
        ]);

        SendEmailJob::dispatch($email, $account);

        return ['success' => true, 'message' => "Email queued to {$toEmail}", 'email_id' => $emailId];
    }

    private function renderInvoiceHtml(object $transaction, $items, $payments, ?object $customer, ?object $business, string $currency): string
    {
        $companyName = $business->name ?? 'Your Business';
        $companyAddress = $business->address ?? '';
        $companyPhone = $business->phone ?? '';
        $companyTin = $business->tin ?? '';

        $customerName = $customer->full_name ?? $transaction->customer_name ?? 'Walk-in Customer';
        $customerEmail = $customer->email ?? '';
        $customerPhone = $customer->phone ?? '';

        $itemRows = '';
        foreach ($items as $i => $item) {
            $itemRows .= "<tr>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;'>" . e($item->product_name) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:center;'>" . number_format($item->quantity) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;'>" . $currency . ' ' . number_format($item->unit_price) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;'>" . $currency . ' ' . number_format($item->line_total) . "</td>
            </tr>";
        }

        $paymentRows = '';
        foreach ($payments as $p) {
            $paymentRows .= "<tr>
                <td style='padding:8px 12px;border-bottom:1px solid #f1f5f9;'>" . e($p->payment_method) . "</td>
                <td style='padding:8px 12px;border-bottom:1px solid #f1f5f9;text-align:right;'>" . $currency . ' ' . number_format($p->amount_paid) . "</td>
                <td style='padding:8px 12px;border-bottom:1px solid #f1f5f9;'>" . e($p->reference_number ?? '—') . "</td>
            </tr>";
        }

        $balanceDue = (float) $transaction->total_amount - (float) $transaction->amount_paid;

        return "
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:680px;margin:0 auto;background:#ffffff;'>
            <div style='background:#1e293b;padding:32px;text-align:center;'>
                <h1 style='color:#fff;font-size:24px;margin:0;'>{$companyName}</h1>
                <p style='color:#94a3b8;font-size:13px;margin:6px 0 0;'>{$companyAddress}" . ($companyPhone ? " | {$companyPhone}" : '') . ($companyTin ? " | TIN: {$companyTin}" : '') . "</p>
            </div>

            <div style='padding:32px;'>
                <div style='display:flex;justify-content:space-between;margin-bottom:24px;'>
                    <div>
                        <h2 style='font-size:20px;font-weight:700;color:#0f172a;margin:0 0 4px;'>INVOICE</h2>
                        <p style='color:#64748b;font-size:13px;margin:0;'>{$transaction->receipt_number}</p>
                        <p style='color:#64748b;font-size:13px;margin:4px 0 0;'>" . \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y, g:i A') . "</p>
                    </div>
                    <div style='text-align:right;'>
                        <div style='display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:" . ($balanceDue <= 0 ? '#dcfce7;color:#166534' : '#fef3c7;color:#92400e') . ";'>" . ($balanceDue <= 0 ? 'PAID' : 'PENDING') . "</div>
                    </div>
                </div>

                <div style='display:flex;gap:24px;margin-bottom:24px;'>
                    <div style='flex:1;background:#f8fafc;border-radius:8px;padding:16px;'>
                        <p style='font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;margin:0 0 8px;'>Bill To</p>
                        <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;'>{$customerName}</p>" .
                        ($customerEmail ? "<p style='font-size:13px;color:#64748b;margin:4px 0 0;'>{$customerEmail}</p>" : '') .
                        ($customerPhone ? "<p style='font-size:13px;color:#64748b;margin:2px 0 0;'>{$customerPhone}</p>" : '') .
                    "</div>
                </div>

                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Item</th>
                            <th style='padding:10px 12px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Qty</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Price</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>{$itemRows}</tbody>
                </table>

                <div style='display:flex;justify-content:flex-end;'>
                    <div style='width:280px;'>
                        <div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#64748b;'>
                            <span>Subtotal</span><span>" . $currency . ' ' . number_format($transaction->subtotal) . "</span>
                        </div>" .
                        ((float)$transaction->discount_amount > 0 ? "<div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#dc2626;'><span>Discount</span><span>- " . $currency . ' ' . number_format($transaction->discount_amount) . "</span></div>" : '') .
                        ((float)$transaction->tax_amount > 0 ? "<div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#64748b;'><span>Tax</span><span>" . $currency . ' ' . number_format($transaction->tax_amount) . "</span></div>" : '') .
                        "<div style='display:flex;justify-content:space-between;padding:12px 0 8px;font-size:18px;font-weight:700;color:#0f172a;border-top:2px solid #0f172a;margin-top:8px;'>
                            <span>Total</span><span>" . $currency . ' ' . number_format($transaction->total_amount) . "</span>
                        </div>
                        <div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#10b981;font-weight:600;'>
                            <span>Paid</span><span>" . $currency . ' ' . number_format($transaction->amount_paid) . "</span>
                        </div>" .
                        ($balanceDue > 0 ? "<div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#ef4444;font-weight:600;'><span>Balance Due</span><span>" . $currency . ' ' . number_format($balanceDue) . "</span></div>" : '') .
                    "</div>
                </div>" .

                ($paymentRows ? "
                <div style='margin-top:24px;'>
                    <h3 style='font-size:14px;font-weight:600;color:#374151;margin:0 0 8px;'>Payments</h3>
                    <table style='width:100%;border-collapse:collapse;'>
                        <thead>
                            <tr style='background:#f8fafc;'>
                                <th style='padding:8px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;'>Method</th>
                                <th style='padding:8px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;'>Amount</th>
                                <th style='padding:8px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;'>Reference</th>
                            </tr>
                        </thead>
                        <tbody>{$paymentRows}</tbody>
                    </table>
                </div>" : '') .

                "</div>

            <div style='background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e5e7eb;'>
                <p style='font-size:12px;color:#94a3b8;margin:0;'>Thank you for your business</p>
            </div>
        </div>";
    }

    private function renderReceiptHtml(object $transaction, $items, $payments, ?object $customer, ?object $business, string $currency): string
    {
        return $this->renderInvoiceHtml($transaction, $items, $payments, $customer, $business, $currency);
    }

    private function renderQuotationHtml(object $quotation, $items, ?object $customer, ?object $business, string $currency): string
    {
        $companyName = $business->name ?? 'Your Business';
        $companyAddress = $business->address ?? '';
        $companyPhone = $business->phone ?? '';

        $customerName = $customer->full_name ?? $quotation->customer_name ?? 'Walk-in Customer';
        $customerEmail = $customer->email ?? '';

        $itemRows = '';
        foreach ($items as $item) {
            $itemRows .= "<tr>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;'>" . e($item->product_name) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:center;'>" . number_format($item->quantity) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;'>" . $currency . ' ' . number_format($item->unit_price) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;'>" . $currency . ' ' . number_format($item->line_total) . "</td>
            </tr>";
        }

        $validUntil = \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y');

        return "
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:680px;margin:0 auto;background:#fff;'>
            <div style='background:#1e40af;padding:32px;text-align:center;'>
                <h1 style='color:#fff;font-size:24px;margin:0;'>{$companyName}</h1>
                <p style='color:#bfdbfe;font-size:13px;margin:6px 0 0;'>{$companyAddress}" . ($companyPhone ? " | {$companyPhone}" : '') . "</p>
            </div>

            <div style='padding:32px;'>
                <div style='display:flex;justify-content:space-between;margin-bottom:24px;'>
                    <div>
                        <h2 style='font-size:20px;font-weight:700;color:#0f172a;margin:0 0 4px;'>QUOTATION</h2>
                        <p style='color:#64748b;font-size:13px;margin:0;'>{$quotation->quote_number}</p>
                        <p style='color:#64748b;font-size:13px;margin:4px 0 0;'>Date: " . \Carbon\Carbon::parse($quotation->created_at)->format('d M Y') . "</p>
                    </div>
                    <div style='text-align:right;'>
                        <div style='display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#dbeafe;color:#1e40af;'>VALID UNTIL {$validUntil}</div>
                    </div>
                </div>

                <div style='background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:24px;'>
                    <p style='font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;margin:0 0 8px;'>Prepared For</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;'>{$customerName}</p>" .
                    ($customerEmail ? "<p style='font-size:13px;color:#64748b;margin:4px 0 0;'>{$customerEmail}</p>" : '') .
                "</div>

                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Item</th>
                            <th style='padding:10px 12px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Qty</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Price</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>{$itemRows}</tbody>
                </table>

                <div style='display:flex;justify-content:flex-end;'>
                    <div style='width:280px;'>
                        <div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#64748b;'>
                            <span>Subtotal</span><span>" . $currency . ' ' . number_format($quotation->subtotal) . "</span>
                        </div>" .
                        ((float)$quotation->discount_amount > 0 ? "<div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#dc2626;'><span>Discount</span><span>- " . $currency . ' ' . number_format($quotation->discount_amount) . "</span></div>" : '') .
                        ((float)$quotation->tax_amount > 0 ? "<div style='display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#64748b;'><span>Tax</span><span>" . $currency . ' ' . number_format($quotation->tax_amount) . "</span></div>" : '') .
                        "<div style='display:flex;justify-content:space-between;padding:12px 0 8px;font-size:18px;font-weight:700;color:#0f172a;border-top:2px solid #0f172a;margin-top:8px;'>
                            <span>Total</span><span>" . $currency . ' ' . number_format($quotation->total_amount) . "</span>
                        </div>
                    </div>
                </div>" .

                ($quotation->notes ? "<div style='margin-top:24px;background:#f8fafc;border-radius:8px;padding:16px;'><h3 style='font-size:14px;font-weight:600;color:#374151;margin:0 0 8px;'>Notes</h3><div style='font-size:13px;color:#64748b;'>{$quotation->notes}</div></div>" : '') .

                "</div>

            <div style='background:#eff6ff;padding:20px 32px;text-align:center;border-top:1px solid #dbeafe;'>
                <p style='font-size:12px;color:#1e40af;margin:0;'>This quotation is valid until {$validUntil}. Please contact us if you have any questions.</p>
            </div>
        </div>";
    }

    private function renderStatementHtml(object $customer, $ledger, ?object $business, string $currency): string
    {
        $companyName = $business->name ?? 'Your Business';
        $customerName = $customer->full_name ?? 'Customer';

        $ledgerRows = '';
        foreach ($ledger as $entry) {
            $typeColor = match($entry->type) {
                'credit', 'layaway' => '#ef4444',
                'credit_payment', 'layaway_payment' => '#10b981',
                'refund' => '#f59e0b',
                default => '#64748b',
            };
            $amountPrefix = in_array($entry->type, ['credit', 'layaway', 'refund']) ? '+' : '-';
            $ledgerRows .= "<tr>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;'>" . \Carbon\Carbon::parse($entry->date)->format('d M Y') . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;'>" . e($entry->description) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:{$typeColor};font-weight:600;'>{$amountPrefix}" . $currency . ' ' . number_format(abs((float)$entry->amount)) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;font-weight:600;'>" . $currency . ' ' . number_format((float)$entry->balance_after) . "</td>
            </tr>";
        }

        $balance = (float) $customer->balance;

        return "
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:680px;margin:0 auto;background:#fff;'>
            <div style='background:#7c3aed;padding:32px;text-align:center;'>
                <h1 style='color:#fff;font-size:24px;margin:0;'>{$companyName}</h1>
                <p style='color:#ddd6fe;font-size:13px;margin:6px 0 0;'>Account Statement</p>
            </div>

            <div style='padding:32px;'>
                <div style='display:flex;justify-content:space-between;margin-bottom:24px;'>
                    <div>
                        <p style='font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;margin:0 0 4px;'>Customer</p>
                        <p style='font-size:16px;font-weight:600;color:#0f172a;margin:0;'>{$customerName}</p>
                        <p style='font-size:13px;color:#64748b;margin:4px 0 0;'>{$customer->email}</p>
                    </div>
                    <div style='text-align:right;'>
                        <p style='font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;margin:0 0 4px;'>Outstanding Balance</p>
                        <p style='font-size:22px;font-weight:700;color:" . ($balance > 0 ? '#ef4444' : '#10b981') . ";margin:0;'>" . $currency . ' ' . number_format($balance) . "</p>
                        <p style='font-size:13px;color:#64748b;margin:4px 0 0;'>As of " . now()->format('d M Y') . "</p>
                    </div>
                </div>

                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Date</th>
                            <th style='padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Description</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Amount</th>
                            <th style='padding:10px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:600;'>Balance</th>
                        </tr>
                    </thead>
                    <tbody>" . ($ledgerRows ?: "<tr><td colspan='4' style='padding:20px;text-align:center;color:#94a3b8;font-size:13px;'>No transactions found</td></tr>") . "</tbody>
                </table>
            </div>

            <div style='background:#f5f3ff;padding:20px 32px;text-align:center;border-top:1px solid #e9d5ff;'>
                <p style='font-size:12px;color:#7c3aed;margin:0;'>If you have any questions about your account, please contact us.</p>
            </div>
        </div>";
    }

    private function renderPaymentReminderHtml(object $transaction, ?object $customer, ?object $business, float $balanceDue, string $currency): string
    {
        $companyName = $business->name ?? 'Your Business';
        $companyPhone = $business->phone ?? '';
        $customerName = $customer->full_name ?? $transaction->customer_name ?? 'Valued Customer';
        $dueDate = $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('d M Y') : 'N/A';
        $daysOverdue = $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->diffInDays(now(), false) : 0;
        $isOverdue = $daysOverdue > 0;

        return "
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:680px;margin:0 auto;background:#fff;'>
            <div style='background:" . ($isOverdue ? '#dc2626' : '#f59e0b') . ";padding:32px;text-align:center;'>
                <h1 style='color:#fff;font-size:24px;margin:0;'>{$companyName}</h1>
                <p style='color:rgba(255,255,255,0.8);font-size:14px;margin:6px 0 0;'>Payment Reminder</p>
            </div>

            <div style='padding:32px;'>
                <p style='font-size:16px;color:#374151;margin:0 0 16px;'>Dear {$customerName},</p>
                <p style='font-size:14px;color:#64748b;margin:0 0 16px;'>This is a friendly reminder regarding your outstanding balance for the following transaction:</p>

                <div style='background:#f8fafc;border-radius:12px;padding:20px;margin-bottom:24px;'>
                    <div style='display:flex;justify-content:space-between;margin-bottom:12px;'>
                        <span style='font-size:13px;color:#64748b;'>Invoice Number</span>
                        <span style='font-size:13px;font-weight:600;color:#0f172a;'>{$transaction->receipt_number}</span>
                    </div>
                    <div style='display:flex;justify-content:space-between;margin-bottom:12px;'>
                        <span style='font-size:13px;color:#64748b;'>Transaction Date</span>
                        <span style='font-size:13px;color:#0f172a;'>" . \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') . "</span>
                    </div>
                    <div style='display:flex;justify-content:space-between;margin-bottom:12px;'>
                        <span style='font-size:13px;color:#64748b;'>Due Date</span>
                        <span style='font-size:13px;color:" . ($isOverdue ? '#dc2626' : '#0f172a') . ";font-weight:" . ($isOverdue ? '700' : '400') . ";'>{$dueDate}" . ($isOverdue ? " ({$daysOverdue} days overdue)" : '') . "</span>
                    </div>
                    <div style='display:flex;justify-content:space-between;margin-bottom:12px;'>
                        <span style='font-size:13px;color:#64748b;'>Total Amount</span>
                        <span style='font-size:13px;color:#0f172a;'>" . $currency . ' ' . number_format((float)$transaction->total_amount) . "</span>
                    </div>
                    <div style='display:flex;justify-content:space-between;padding-top:12px;border-top:2px solid #e5e7eb;'>
                        <span style='font-size:15px;font-weight:700;color:#0f172a;'>Balance Due</span>
                        <span style='font-size:20px;font-weight:700;color:#dc2626;'>" . $currency . ' ' . number_format($balanceDue) . "</span>
                    </div>
                </div>

                <p style='font-size:14px;color:#64748b;margin:0 0 16px;'>Please arrange payment at your earliest convenience. If you have already made this payment, please disregard this reminder.</p>

                <p style='font-size:14px;color:#64748b;margin:0;'>If you have any questions, please contact us" . ($companyPhone ? " at {$companyPhone}" : '') . ".</p>
            </div>

            <div style='background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e5e7eb;'>
                <p style='font-size:12px;color:#94a3b8;margin:0;'>Thank you for your prompt payment</p>
            </div>
        </div>";
    }
}
