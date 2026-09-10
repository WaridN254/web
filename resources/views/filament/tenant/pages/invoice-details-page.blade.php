<x-filament-panels::page>
    <style>
        .dreams-invoice-detail {
            --id-navy: #1e293b;
            --id-ink: #334155;
            --id-muted: #64748b;
            --id-border: #e2e8f0;
            --id-head-bg: #f1f5f9;
            --id-white: #ffffff;
            --id-orange: #f97316;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--id-ink);
        }

        .dreams-invoice-detail * { box-sizing: border-box; }

        .dreams-invoice-detail .id-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .dreams-invoice-detail .id-page-title h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-head-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dreams-invoice-detail .id-head-action {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: var(--id-white);
            border: 1px solid var(--id-border);
            border-radius: 6px;
            color: var(--id-muted);
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .dreams-invoice-detail .id-head-action:hover {
            background: var(--id-head-bg);
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 36px;
            padding: 0 16px;
            background: var(--id-orange);
            color: #ffffff;
            border: 0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s ease;
        }

        .dreams-invoice-detail .id-back-btn:hover { background: #ea580c; }

        .dreams-invoice-detail .id-card {
            background: var(--id-white);
            border: 1px solid var(--id-border);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
            padding: 28px 32px;
        }

        .dreams-invoice-detail .id-head-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--id-border);
            margin-bottom: 22px;
        }

        .dreams-invoice-detail .id-company-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--id-navy);
            margin: 0 0 6px;
        }

        .dreams-invoice-detail .id-company-line {
            margin: 3px 0;
            font-size: 13px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-invoice-number {
            text-align: right;
        }

        .dreams-invoice-detail .id-invoice-number h5 {
            margin: 0 0 8px;
            font-size: 15px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-invoice-number h5 span {
            color: var(--id-orange);
        }

        .dreams-invoice-detail .id-date-line {
            margin: 4px 0;
            font-size: 13px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-date-line strong {
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-parties {
            display: grid;
            grid-template-columns: 5fr 5fr 2fr;
            gap: 24px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--id-border);
            margin-bottom: 22px;
        }

        .dreams-invoice-detail .id-party-label {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-party-name {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 600;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-party-line {
            margin: 3px 0;
            font-size: 13px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-party-line strong { color: var(--id-ink); }

        .dreams-invoice-detail .id-status-label {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .dreams-invoice-detail .id-badge svg { width: 8px; height: 8px; }

        .dreams-invoice-detail .id-badge.paid { background: #dcfce7; color: #15803d; }
        .dreams-invoice-detail .id-badge.unpaid { background: #fee2e2; color: #b91c1c; }
        .dreams-invoice-detail .id-badge.overdue { background: #fef3c7; color: #b45309; }

        .dreams-invoice-detail .id-summary-title {
            margin: 0 0 14px;
            font-size: 14px;
            font-weight: 600;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-table-wrap { overflow-x: auto; }

        .dreams-invoice-detail table.id-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 720px;
        }

        .dreams-invoice-detail .id-table thead th {
            background: var(--id-head-bg);
            color: var(--id-navy);
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            padding: 11px 14px;
            border-bottom: 1px solid var(--id-border);
            white-space: nowrap;
        }

        .dreams-invoice-detail .id-table thead th.right { text-align: right; }

        .dreams-invoice-detail .id-table tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #eef1f4;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-table tbody tr:last-child td { border-bottom: 0; }

        .dreams-invoice-detail .id-table tbody td.right { text-align: right; }

        .dreams-invoice-detail .id-prod-name {
            color: var(--id-navy);
            font-weight: 500;
        }

        .dreams-invoice-detail .id-totals {
            display: flex;
            justify-content: flex-end;
            padding-top: 20px;
        }

        .dreams-invoice-detail .id-totals-box {
            width: 420px;
            max-width: 100%;
        }

        .dreams-invoice-detail .id-total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 4px;
            border-bottom: 1px solid #eef1f4;
            font-size: 13px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-total-line span:last-child {
            color: var(--id-navy);
            font-weight: 500;
        }

        .dreams-invoice-detail .id-total-line.grand span {
            font-size: 16px;
            font-weight: 700;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-amount-words {
            margin-top: 10px;
            font-size: 12px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-footer-row {
            display: grid;
            grid-template-columns: 7fr 5fr;
            gap: 24px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--id-border);
        }

        .dreams-invoice-detail .id-notes h6 {
            margin: 0 0 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-notes p {
            margin: 0 0 12px;
            font-size: 13px;
            color: var(--id-muted);
            line-height: 1.6;
        }

        .dreams-invoice-detail .id-signature { text-align: right; }

        .dreams-invoice-detail .id-sign-name {
            margin: 0 0 2px;
            font-size: 13px;
            font-weight: 600;
            color: var(--id-navy);
        }

        .dreams-invoice-detail .id-sign-role {
            margin: 0;
            font-size: 12px;
            color: var(--id-muted);
        }

        .dreams-invoice-detail .id-footer-btns {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 24px;
        }

        .dreams-invoice-detail .id-bottom-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 22px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s ease;
        }

        .dreams-invoice-detail .id-bottom-btn.print {
            background: var(--id-orange);
            color: #ffffff;
            border: 0;
        }

        .dreams-invoice-detail .id-bottom-btn.print:hover { background: #ea580c; }

        .dreams-invoice-detail .id-bottom-btn.clone {
            background: var(--id-white);
            color: var(--id-navy);
            border: 1px solid var(--id-border);
        }

        .dreams-invoice-detail .id-bottom-btn.clone:hover { background: var(--id-head-bg); }

        @media (max-width: 900px) {
            .dreams-invoice-detail .id-parties { grid-template-columns: 1fr; }
            .dreams-invoice-detail .id-footer-row { grid-template-columns: 1fr; }
        }

        @media print {
            body * { visibility: hidden; }
            .dreams-invoice-detail, .dreams-invoice-detail * { visibility: visible; }
            .dreams-invoice-detail { position: absolute; inset: 0; padding: 24px; }
            .dreams-invoice-detail .id-page-header,
            .dreams-invoice-detail .id-footer-btns { display: none !important; }
            .dreams-invoice-detail .id-card { box-shadow: none; border-color: #e2e8f0; }
        }
    </style>

    <div class="dreams-invoice-detail">
        @php
            $company = $this->companyInfo();
            $customer = $this->getCustomer();
            $branch = $this->getBranch();
            $items = $this->getItems();
            $payments = $this->getPayments();
            $paid = (float) ($this->invoice->amount_paid ?? 0);
            $total = (float) ($this->invoice->total_amount ?? 0);
            $due = (float) ($this->invoice->balance_due ?? ($total - $paid));
            $status = $due <= 0 ? 'Paid' : ($this->invoice->transaction_date?->copy()->addDays(15)?->lt(now()) ? 'Overdue' : 'Unpaid');
            $badge = strtolower($status);
            $currency = $company['currency'] ?? 'UGX';
        @endphp

        <div class="id-page-header">
            <div class="id-page-title">
                <h4>Invoice Details</h4>
            </div>
            <div class="id-head-right">
                <button type="button" wire:click="openSendEmailModal('invoice', '{{ $this->invoice->id ?? '' }}', '{{ $customer?->email ?? '' }}')" title="Send by Email" style="width:34px;height:34px;display:grid;place-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:6px;color:#7c3aed;cursor:pointer;transition:background .15s ease,color .15s ease;" onmouseover="this.style.background='#f5f3ff';this.style.color='#6d28d9'" onmouseout="this.style.background='#fff';this.style.color='#7c3aed'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
                <a class="id-head-action" title="Print" href="javascript:window.print();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/></svg>
                </a>
                <a class="id-back-btn" href="{{ $this->getInvoiceUrl() }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Back to Invoices
                </a>
            </div>
        </div>

        <div class="id-card">
            <div class="id-head-row">
                <div>
                    <p class="id-company-name">{{ $company['name'] }}</p>
                    @if ($company['address'])
                        <p class="id-company-line">{{ $company['address'] }}</p>
                    @endif
                    @if ($company['email'])
                        <p class="id-company-line">Email : <strong>{{ $company['email'] }}</strong></p>
                    @endif
                    @if ($company['phone'])
                        <p class="id-company-line">Phone : <strong>{{ $company['phone'] }}</strong></p>
                    @endif
                </div>
                <div class="id-invoice-number">
                    <h5>Invoice No <span>#{{ $this->invoice->receipt_number ?? $this->invoice->id }}</span></h5>
                    <p class="id-date-line">Created Date : <strong>{{ optional($this->invoice->transaction_date)->format('M d, Y') ?? '-' }}</strong></p>
                    <p class="id-date-line">Due Date : <strong>{{ optional($this->invoice->transaction_date?->copy()->addDays(15))->format('M d, Y') ?? '-' }}</strong></p>
                </div>
            </div>

            <div class="id-parties">
                <div>
                    <p class="id-party-label">From</p>
                    <p class="id-party-name">{{ $company['name'] }}</p>
                    @if ($company['address'])
                        <p class="id-party-line">{{ $company['address'] }}</p>
                    @endif
                    @if ($company['email'])
                        <p class="id-party-line">Email : <strong>{{ $company['email'] }}</strong></p>
                    @endif
                    @if ($company['phone'])
                        <p class="id-party-line">Phone : <strong>{{ $company['phone'] }}</strong></p>
                    @endif
                </div>
                <div>
                    <p class="id-party-label">To</p>
                    <p class="id-party-name">{{ $customer?->full_name ?? ($this->invoice->customer_name ?? 'Walk-in Customer') }}</p>
                    @if ($customer?->address)
                        <p class="id-party-line">{{ $customer->address }}</p>
                    @endif
                    @if ($customer?->email)
                        <p class="id-party-line">Email : <strong>{{ $customer->email }}</strong></p>
                    @endif
                    @if ($customer?->phone)
                        <p class="id-party-line">Phone : <strong>{{ $customer->phone }}</strong></p>
                    @endif
                </div>
                <div>
                    <p class="id-party-label">Branch</p>
                    <p class="id-party-name">{{ $branch?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="id-status-label">Payment Status</p>
                    <span class="id-badge {{ $badge }}">
                        <svg viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                        {{ $status }}
                    </span>
                </div>
            </div>

            <p class="id-summary-title">Order Summary</p>
            <div class="id-table-wrap">
                <table class="id-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="right">Qty</th>
                            <th class="right">Unit Price ({{ $currency }})</th>
                            <th class="right">Discount ({{ $currency }})</th>
                            <th class="right">Total ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="id-prod-name">{{ $item->product_name ?? 'Item' }}</td>
                                <td class="right">{{ $item->quantity }}</td>
                                <td class="right">{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                                <td class="right">{{ number_format((float) ($item->discount_applied ?? $item->discount ?? 0), 2) }}</td>
                                <td class="right">{{ number_format((float) ($item->line_total ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--id-muted);">No items found for this invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="id-totals">
                <div class="id-totals-box">
                    <div class="id-total-line">
                        <span>Sub Total</span>
                        <span>{{ number_format((float) $this->invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="id-total-line">
                        <span>Discount</span>
                        <span>{{ number_format((float) $this->invoice->discount_amount, 2) }}</span>
                    </div>
                    <div class="id-total-line">
                        <span>Tax</span>
                        <span>{{ number_format((float) $this->invoice->tax_amount, 2) }}</span>
                    </div>
                    <div class="id-total-line grand">
                        <span>Total Amount</span>
                        <span>{{ $currency }} {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="id-total-line">
                        <span>Paid</span>
                        <span>{{ number_format($paid, 2) }}</span>
                    </div>
                    <div class="id-total-line">
                        <span>Amount Due</span>
                        <span>{{ number_format($due, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="id-footer-row">
                <div class="id-notes">
                    <div style="margin-bottom:12px;">
                        <h6>Terms and Conditions</h6>
                        <p>Please pay within 15 days from the date of invoice. A late payment charge of 14% may apply on overdue balances.</p>
                    </div>
                    <div>
                        <h6>Notes</h6>
                        <p>Please quote the invoice number when remitting funds.</p>
                    </div>
                </div>
                <div class="id-signature">
                    <p class="id-sign-name">{{ $company['name'] }}</p>
                    <p class="id-sign-role">Authorized Signatory</p>
                </div>
            </div>
        </div>

        <div class="id-footer-btns">
            <a href="javascript:window.print();" class="id-bottom-btn print">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/></svg>
                Print Invoice
            </a>
        </div>
    </div>

    @if($showSendEmailModal)
    <div style="position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" wire:click.self="closeSendEmailModal">
        <div style="background:#fff;border-radius:14px;width:92%;max-width:540px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden;">

            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">
                    @if($sendEmailDocType === 'invoice') Send Invoice
                    @elseif($sendEmailDocType === 'receipt') Send Receipt
                    @elseif($sendEmailDocType === 'quotation') Send Quotation
                    @elseif($sendEmailDocType === 'statement') Send Statement
                    @elseif($sendEmailDocType === 'payment_reminder') Send Payment Reminder
                    @else Send Email
                    @endif
                </h3>
                <button type="button" wire:click="closeSendEmailModal" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;padding:2px 6px;border-radius:6px;">&times;</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:20px;">
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">To</label>
                    <input type="email" wire:model.live="sendEmailTo" placeholder="customer@example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                    @error('sendEmailTo') <span style="color:#dc2626;font-size:12px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Subject</label>
                    <input type="text" wire:model.live="sendEmailSubject" placeholder="Email subject..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:0;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Message (optional)</label>
                    <textarea wire:model.live="sendEmailMessage" rows="3" placeholder="Add a personal note..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
                </div>
            </div>

            @if($sendEmailResult)
            <div style="padding:12px 20px;font-size:13px;@if($sendEmailSuccess) background:#dcfce7;color:#166534;@else background:#fee2e2;color:#991b1b;@endif border-top:1px solid @if($sendEmailSuccess) #bbf7d0;@else #fecaca;@endif">
                {{ $sendEmailResult }}
            </div>
            @endif

            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" wire:click="closeSendEmailModal" style="padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#475569;">Cancel</button>
                <button type="button" wire:click="sendEmailFromModal" {{ $sendEmailSending ? 'disabled' : '' }} style="padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:{{ $sendEmailSending ? '#94a3b8' : '#2563eb' }};color:#fff;display:flex;align-items:center;gap:6px;">
                    @if($sendEmailSending)
                        <svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75;"></path></svg>
                        Sending...
                    @else
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Send Email
                    @endif
                </button>
            </div>
        </div>
    </div>
    <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
    @endif
</x-filament-panels::page>