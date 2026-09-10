@php
    $customer = $this->selectedCustomer();
    $currency = $this->currency();
    $customers = $this->customers();
    $creditSummary = $this->creditSummary();
    $layawaySummary = $this->layawaySummary();
    $canSettle = $this->canSettlePayments();
@endphp

<x-filament-panels::page>
<style>
    .dreams-ledger-page {
        --cl-navy: #102a43;
        --cl-ink: #334155;
        --cl-muted: #6b7280;
        --cl-border: #e3e8ed;
        --cl-orange: #f97316;
        --cl-green: #0e9384;
        --cl-red: #b91c1c;
        --cl-white: #ffffff;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        color: var(--cl-ink);
    }

    .dreams-ledger-page * { box-sizing: border-box; }

    [x-cloak] { display: none !important; }

    .cl-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 20px;
    }

    .cl-page-title h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--cl-navy);
    }

    .cl-page-title h6 {
        margin: 4px 0 0;
        font-size: 13px;
        font-weight: 400;
        color: var(--cl-muted);
    }

    .cl-customer-select {
        height: 40px;
        min-width: 260px;
        border: 1px solid var(--cl-border);
        border-radius: 8px;
        background: var(--cl-white);
        color: #243447;
        font-size: 13px;
        font-weight: 600;
        padding: 0 12px;
        outline: none;
        cursor: pointer;
    }

    .cl-customer-select:focus {
        border-color: var(--cl-orange);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }

    .cl-card {
        background: var(--cl-white);
        border: 1px solid var(--cl-border);
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }

    .cl-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--cl-border);
    }

    .cl-avatar-fallback {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        flex: 0 0 40px;
        border-radius: 10px;
        background: var(--cl-orange);
        color: #ffffff;
        font-size: 15px;
        font-weight: 800;
    }

    .cl-customer-name {
        margin: 0;
        font-size: 15px;
        font-weight: 650;
        color: var(--cl-navy);
    }

    .cl-customer-meta {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--cl-muted);
    }

    .cl-head-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cl-pill {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #f1f5f9;
        color: var(--cl-navy);
    }

    .cl-pill.is-red { background: #fef2f2; color: var(--cl-red); }
    .cl-pill.is-green { background: #ecfdf5; color: #047857; }

    .cl-card-body {
        padding: 20px;
    }

    .cl-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .cl-stat {
        background: #f8fafc;
        border: 1px solid var(--cl-border);
        border-radius: 10px;
        padding: 14px 16px;
    }

    .cl-stat-label {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--cl-muted);
    }

    .cl-stat-value {
        margin: 6px 0 0;
        font-size: 19px;
        font-weight: 800;
        color: var(--cl-navy);
    }

    .cl-stat-value.is-green { color: #047857; }
    .cl-stat-value.is-red { color: var(--cl-red); }

    .cl-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--cl-border);
        margin-bottom: 18px;
    }

    .cl-tab {
        padding: 10px 18px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--cl-muted);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
    }

    .cl-tab.is-active {
        color: var(--cl-orange);
        border-bottom-color: var(--cl-orange);
    }

    .cl-tab-badge {
        background: #fee2e2;
        color: #b91c1c;
        font-size: 10px;
        font-weight: 800;
        border-radius: 999px;
        padding: 1px 7px;
    }

    .cl-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--cl-border);
        border-radius: 10px;
    }

    .cl-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    .cl-table th {
        text-align: left;
        padding: 11px 14px;
        background: #f8fafc;
        color: var(--cl-navy);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid var(--cl-border);
        white-space: nowrap;
    }

    .cl-table td {
        padding: 11px 14px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12.5px;
        color: var(--cl-ink);
    }

    .cl-table tr:last-child td { border-bottom: 0; }

    .cl-amount {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    .cl-amount.is-negative { color: #047857; }
    .cl-amount.is-positive { color: var(--cl-red); }

    .cl-type-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .cl-type-badge.is-credit { background: #ffedd5; color: #c2410c; }
    .cl-type-badge.is-layaway { background: #ede9fe; color: #6d28d9; }
    .cl-type-badge.is-payment { background: #ecfdf5; color: #047857; }

    .cl-status-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
    }

    .cl-status-badge.is-active { background: #fef3c7; color: #92400e; }
    .cl-status-badge.is-done { background: #ecfdf5; color: #047857; }

    .cl-section-title {
        margin: 22px 0 10px;
        font-size: 13px;
        font-weight: 800;
        color: var(--cl-navy);
    }

    .cl-empty {
        text-align: center;
        padding: 30px 16px;
        color: var(--cl-muted);
        font-size: 13px;
    }

    .cl-empty strong {
        display: block;
        color: var(--cl-navy);
        margin-bottom: 4px;
        font-size: 14px;
    }

    .cl-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        background: transparent;
        color: var(--cl-navy);
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .cl-btn.is-primary {
        background: var(--cl-orange);
        color: #ffffff;
    }

    .cl-btn.is-primary:hover { background: #ea6a09; }

    .cl-btn.is-ghost { border-color: var(--cl-border); background: var(--cl-white); }

    .cl-btn.is-ghost:hover { border-color: #cbd5e1; background: #f8fafc; }

    .cl-overlay {
        position: fixed;
        inset: 0;
        z-index: 120;
        background: rgba(15, 23, 42, .45);
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .cl-modal {
        width: 100%;
        max-width: 440px;
        max-height: 92vh;
        overflow-y: auto;
        background: var(--cl-white);
        border-radius: 12px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, .25);
        display: flex;
        flex-direction: column;
    }

    .cl-modal.is-receipt { max-width: 380px; }

    .cl-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--cl-border);
    }

    .cl-modal-head h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 750;
        color: var(--cl-navy);
    }

    .cl-modal-sub {
        margin: 4px 0 0;
        font-size: 12px;
        color: var(--cl-muted);
    }

    .cl-modal-close {
        border: 0;
        background: transparent;
        font-size: 22px;
        line-height: 1;
        color: var(--cl-muted);
        cursor: pointer;
        padding: 0 2px;
    }

    .cl-modal-close:hover { color: var(--cl-red); }

    .cl-modal-body {
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cl-modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px;
        border-top: 1px solid var(--cl-border);
    }

    .cl-field-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--cl-muted);
        margin: 2px 0 -4px;
    }

    .cl-field {
        width: 100%;
        height: 40px;
        border: 1px solid var(--cl-border);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        color: #243447;
        outline: none;
        background: var(--cl-white);
    }

    .cl-field:focus {
        border-color: var(--cl-orange);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }

    .cl-invoice-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 14px;
        background: #f8fafc;
        border: 1px solid var(--cl-border);
        border-radius: 10px;
        padding: 12px 14px;
    }

    .cl-invoice-summary div { display: flex; flex-direction: column; gap: 2px; }

    .cl-invoice-summary span { font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--cl-muted); font-weight: 700; }

    .cl-invoice-summary strong { font-size: 12.5px; color: var(--cl-navy); }

    .cl-invoice-summary strong.is-red { color: var(--cl-red); }

    .cl-items {
        margin-top: 10px;
        border: 1px solid var(--cl-border);
        border-radius: 10px;
        overflow: hidden;
    }

    .cl-items-head,
    .cl-items-row {
        display: grid;
        grid-template-columns: 1fr 40px 90px;
        gap: 8px;
        padding: 7px 12px;
        font-size: 11.5px;
        align-items: center;
    }

    .cl-items-head {
        background: #f1f5f9;
        color: var(--cl-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .cl-items-row {
        border-top: 1px solid var(--cl-border);
        color: var(--cl-navy);
    }

    .cl-items-name {
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cl-items-row span:nth-child(2) { text-align: center; }
    .cl-items-row span:nth-child(3) { text-align: right; font-weight: 700; }

    .cl-receipt {
        font-family: "Courier New", ui-monospace, monospace;
        background: #fffdf7;
        border: 1px dashed var(--cl-border);
        border-radius: 8px;
        padding: 16px;
        color: #1f2937;
    }

    .cl-receipt-head {
        text-align: center;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .cl-receipt-head h5 { margin: 0; font-size: 15px; font-weight: 800; color: #111827; }

    .cl-receipt-head p { margin: 3px 0 0; font-size: 11px; color: #4b5563; }

    .cl-receipt-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 11.5px;
        padding: 3px 0;
    }

    .cl-receipt-row.is-total {
        border-top: 1px dashed #cbd5e1;
        margin-top: 6px;
        padding-top: 8px;
        font-weight: 800;
        font-size: 12.5px;
    }

    .cl-receipt-row span:last-child { text-align: right; }

    @media (max-width: 720px) {
        .cl-stats { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="dreams-ledger-page">
    <div class="cl-page-header">
        <div class="cl-page-title">
            <h4>Customer Ledger</h4>
            <h6>Track credit and layaway balances for each customer</h6>
        </div>
        <select class="cl-customer-select" aria-label="Select customer" onchange="window.location.href = '/tenant/customer-ledger?customer=' + encodeURIComponent(this.value) + '&amp;tab={{ $ledgerTab }}'">
            @foreach ($customers as $option)
                <option value="{{ $option->id }}" @selected($option->id === $customer?->id)>{{ $option->full_name }}@if ((float) $option->balance > 0) — {{ $currency }} {{ number_format((float) $option->balance, 0) }} due @endif</option>
            @endforeach
        </select>
    </div>

    @if ($customer)
        <div wire:key="customer-{{ $renderKey }}">
        <div class="cl-card">
            <div class="cl-card-head">
                <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                    <span class="cl-avatar-fallback">{{ collect(explode(' ', trim($customer->full_name)))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') }}</span>
                    <div style="min-width:0;">
                        <p class="cl-customer-name">{{ $customer->full_name }}</p>
                        <p class="cl-customer-meta">
                            @if ($customer->phone) {{ $customer->phone }} &middot; @endif
                            {{ $customer->email ?? 'no email' }}
                        </p>
                    </div>
                </div>
                <div class="cl-head-pills">
                    <span class="cl-pill">Credit limit {{ $currency }} {{ number_format((float) $customer->credit_limit, 0) }}</span>
                    <span class="cl-pill {{ (float) $customer->balance > 0 ? 'is-red' : 'is-green' }}">Account balance {{ $currency }} {{ number_format((float) $customer->balance, 0) }}</span>
                    <span class="cl-pill {{ $customer->credit_enabled ? 'is-green' : '' }}">{{ $customer->credit_enabled ? 'Credit enabled' : 'Credit disabled' }}</span>
                </div>
                @if ($canSettle && (float) $customer->balance > 0 && count($this->openInvoices()) > 0)
                    <button type="button" class="cl-btn is-primary" wire:click="openPaymentModal">
                        <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" /></svg>
                        Receive Payment
                    </button>
                @endif
                <button type="button" class="cl-btn is-ghost" wire:click="openSendEmailModal('statement', '{{ $customer->id }}', '{{ $customer->email ?? '' }}')">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z" /><path d="M19 8.839l-7.5 3.75a2.25 2.25 0 0 1-2 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z" /></svg>
                    Send Statement
                </button>
            </div>

            <div class="cl-card-body">
                <div class="cl-stats">
                    @if ($ledgerTab === 'credit')
                        <div class="cl-stat">
                            <p class="cl-stat-label">Total Credit</p>
                            <p class="cl-stat-value">{{ $currency }} {{ number_format($creditSummary['total'], 0) }}</p>
                        </div>
                        <div class="cl-stat">
                            <p class="cl-stat-label">Paid</p>
                            <p class="cl-stat-value is-green">{{ $currency }} {{ number_format($creditSummary['paid'], 0) }}</p>
                        </div>
                        <div class="cl-stat">
                            <p class="cl-stat-label">Outstanding</p>
                            <p class="cl-stat-value {{ $creditSummary['outstanding'] > 0 ? 'is-red' : 'is-green' }}">{{ $currency }} {{ number_format($creditSummary['outstanding'], 0) }}</p>
                        </div>
                    @else
                        <div class="cl-stat">
                            <p class="cl-stat-label">Total Layaway</p>
                            <p class="cl-stat-value">{{ $currency }} {{ number_format($layawaySummary['total'], 0) }}</p>
                        </div>
                        <div class="cl-stat">
                            <p class="cl-stat-label">Paid</p>
                            <p class="cl-stat-value is-green">{{ $currency }} {{ number_format($layawaySummary['paid'], 0) }}</p>
                        </div>
                        <div class="cl-stat">
                            <p class="cl-stat-label">Outstanding</p>
                            <p class="cl-stat-value {{ $layawaySummary['outstanding'] > 0 ? 'is-red' : 'is-green' }}">{{ $currency }} {{ number_format($layawaySummary['outstanding'], 0) }}</p>
                        </div>
                        <div class="cl-stat">
                            <p class="cl-stat-label">Active Plans</p>
                            <p class="cl-stat-value">{{ $layawaySummary['plans'] }}</p>
                        </div>
                    @endif
                </div>

                <div class="cl-tabs">
                    <a class="cl-tab {{ $ledgerTab === 'credit' ? 'is-active' : '' }}" href="/tenant/customer-ledger?customer={{ $customer->id }}&amp;tab=credit">
                        Credit
                        <span class="cl-tab-badge">{{ $creditSummary['outstanding'] > 0 ? $currency . ' ' . number_format($creditSummary['outstanding'], 0) : '0' }}</span>
                    </a>
                    <a class="cl-tab {{ $ledgerTab === 'layaway' ? 'is-active' : '' }}" href="/tenant/customer-ledger?customer={{ $customer->id }}&amp;tab=layaway">
                        Layaway
                        <span class="cl-tab-badge">{{ $layawaySummary['outstanding'] > 0 ? $currency . ' ' . number_format($layawaySummary['outstanding'], 0) : '0' }}</span>
                    </a>
                </div>

                @if ($ledgerTab === 'credit')
                    @php $entries = $this->creditEntries(); @endphp
                    <div class="cl-table-wrap">
                        <table class="cl-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th style="text-align:right;">Debit</th>
                                    <th style="text-align:right;">Payment</th>
                                    <th style="text-align:right;">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $row)
                                    @php
                                        $entry = $row['entry'];
                                        $isPayment = $entry->type === 'credit_payment';
                                    @endphp
                                    <tr>
                                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($entry->date)->format('d M Y, H:i') }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td>
                                            <span class="cl-type-badge {{ $isPayment ? 'is-payment' : 'is-credit' }}">{{ $isPayment ? 'Payment' : 'Credit' }}</span>
                                        </td>
                                        <td class="cl-amount" style="text-align:right;">{{ $isPayment ? '—' : $currency . ' ' . number_format((float) $entry->amount, 0) }}</td>
                                        <td class="cl-amount" style="text-align:right;">{{ $isPayment ? $currency . ' ' . number_format(abs((float) $entry->amount), 0) : '—' }}</td>
                                        <td class="cl-amount {{ $row['running'] > 0 ? 'is-positive' : 'is-negative' }}" style="text-align:right;">{{ $currency }} {{ number_format(max(0, $row['running']), 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="cl-empty">
                                                <strong>No credit activity</strong>
                                                <span>Credit sales and payments for this customer will appear here.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    @php $entries = $this->layawayEntries(); @endphp
                    <div class="cl-table-wrap">
                        <table class="cl-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th style="text-align:right;">Debit</th>
                                    <th style="text-align:right;">Payment</th>
                                    <th style="text-align:right;">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $row)
                                    @php
                                        $entry = $row['entry'];
                                        $isPayment = $entry->type === 'layaway_payment';
                                    @endphp
                                    <tr>
                                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($entry->date)->format('d M Y, H:i') }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td>
                                            <span class="cl-type-badge {{ $isPayment ? 'is-payment' : 'is-layaway' }}">{{ $isPayment ? 'Payment' : 'Layaway' }}</span>
                                        </td>
                                        <td class="cl-amount" style="text-align:right;">{{ $isPayment ? '—' : $currency . ' ' . number_format((float) $entry->amount, 0) }}</td>
                                        <td class="cl-amount" style="text-align:right;">{{ $isPayment ? $currency . ' ' . number_format(abs((float) $entry->amount), 0) : '—' }}</td>
                                        <td class="cl-amount {{ $row['running'] > 0 ? 'is-positive' : 'is-negative' }}" style="text-align:right;">{{ $currency }} {{ number_format(max(0, $row['running']), 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="cl-empty">
                                                <strong>No layaway activity</strong>
                                                <span>Layaway sales and payments for this customer will appear here.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <p class="cl-section-title">Active Layaway Plans</p>
                    <div class="cl-table-wrap">
                        <table class="cl-table">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th style="text-align:right;">Balance</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->layawayPlans() as $plan)
                                    @php
                                        $planBalance = max(0, (float) $plan->total_amount - (float) $plan->amount_paid);
                                        $overdue = \Carbon\Carbon::parse($plan->due_date)->isPast() && $planBalance > 0;
                                    @endphp
                                    <tr>
                                        <td style="white-space:nowrap;">{{ $plan->transaction_id ? '#' . strtoupper(substr((string) $plan->transaction_id, 0, 8)) : '—' }}</td>
                                        <td class="cl-amount">{{ $currency }} {{ number_format((float) $plan->total_amount, 0) }}</td>
                                        <td class="cl-amount">{{ $currency }} {{ number_format((float) $plan->amount_paid, 0) }}</td>
                                        <td class="cl-amount {{ $planBalance > 0 ? 'is-positive' : 'is-negative' }}" style="text-align:right;">{{ $currency }} {{ number_format($planBalance, 0) }}</td>
                                        <td style="white-space:nowrap;{{ $overdue ? 'color:#b91c1c;font-weight:700;' : '' }}">{{ \Carbon\Carbon::parse($plan->due_date)->format('d M Y') }}{{ $overdue ? ' (overdue)' : '' }}</td>
                                        <td>
                                            <span class="cl-status-badge {{ $plan->status === 'completed' ? 'is-done' : 'is-active' }}">{{ ucfirst($plan->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="cl-empty">
                                                <strong>No active layaway plans</strong>
                                                <span>Plans that are still being paid off will appear here.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        </div>
    @else
        <div class="cl-card">
            <div class="cl-card-body">
                <div class="cl-empty">
                    <strong>No customer selected</strong>
                    <span>Choose a customer from the dropdown above to view their ledger.</span>
                </div>
            </div>
        </div>
    @endif

    @if ($customer && $paymentModalOpen)
        <div class="cl-overlay" wire:click.self="$set('paymentModalOpen', false)">
            <div class="cl-modal">
                <div class="cl-modal-head">
                    <div>
                        <h5>Receive Payment</h5>
                        <p class="cl-modal-sub">Record a payment against an open {{ $customer->full_name }} invoice.</p>
                    </div>
                    <button type="button" class="cl-modal-close" wire:click="$set('paymentModalOpen', false)">&times;</button>
                </div>
                <div class="cl-modal-body">
                    <label class="cl-field-label">Open Invoice</label>
                    <select class="cl-field" wire:model="paymentInvoiceId" wire:change="paymentInvoiceChanged">
                        @foreach ($this->openInvoices() as $inv)
                            <option value="{{ $inv['id'] }}">#{{ $inv['receipt_number'] }} — {{ $currency }} {{ number_format($inv['balance_due'], 0) }} due ({{ ucfirst($inv['transaction_type']) }})</option>
                        @endforeach
                    </select>
                    @if ($selInv = $this->selectedInvoice())
                        <div class="cl-invoice-summary">
                            <div><span>Invoice</span><strong>{{ $selInv['receipt_number'] }}</strong></div>
                            <div><span>Date</span><strong>{{ \Carbon\Carbon::parse($selInv['transaction_date'])->format('d M Y') }}</strong></div>
                            <div><span>Total</span><strong>{{ $currency }} {{ number_format($selInv['total_amount'], 0) }}</strong></div>
                            <div><span>Paid</span><strong>{{ $currency }} {{ number_format($selInv['amount_paid'], 0) }}</strong></div>
                            <div><span>Outstanding</span><strong class="is-red">{{ $currency }} {{ number_format($selInv['balance_due'], 0) }}</strong></div>
                        </div>
                        @if (count($selInv['items']) > 0)
                            <div class="cl-items">
                                <div class="cl-items-head">
                                    <span>Item being paid for</span>
                                    <span>Qty</span>
                                    <span style="text-align:right;">Amount</span>
                                </div>
                                @foreach ($selInv['items'] as $item)
                                    <div class="cl-items-row">
                                        <span class="cl-items-name">{{ $item['name'] }}{{ $item['unit_label'] ? ' (' . $item['unit_label'] . ')' : '' }}</span>
                                        <span>{{ $item['quantity'] }}</span>
                                        <span style="text-align:right;">{{ $currency }} {{ number_format($item['line_total'], 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    <label class="cl-field-label">Amount Received</label>
                    <input class="cl-field" type="number" min="1" step="any" wire:model.debounce.300ms="paymentAmount" placeholder="Due {{ number_format($this->selectedInvoice()['balance_due'] ?? 0, 0) }}" />
                    <label class="cl-field-label">Payment Method</label>
                    <select class="cl-field" wire:model="paymentMethodId">
                        @foreach ($this->paymentMethods() as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cl-modal-foot">
                    <button type="button" class="cl-btn is-ghost" wire:click="$set('paymentModalOpen', false)">Cancel</button>
                    <button type="button" wire:click="recordPayment('{{ $paymentInvoiceId }}')" class="cl-btn is-primary">Record Payment</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPaymentReceipt)
        <div class="cl-overlay" wire:click.self="$set('showPaymentReceipt', false)">
            <div class="cl-modal is-receipt">
                <div class="cl-modal-head">
                    <div>
                        <h5>Payment Receipt</h5>
                        <p class="cl-modal-sub">Reference {{ $paymentReceipt['reference'] }}</p>
                    </div>
                    <button type="button" class="cl-modal-close" wire:click="$set('showPaymentReceipt', false)">&times;</button>
                </div>
                <div class="cl-modal-body">
                    <div class="cl-receipt" id="payment-receipt-print">
                        <div class="cl-receipt-head">
                            <h5>{{ $this->companyName() }}</h5>
                            <p>{{ ucfirst($paymentReceipt['type']) }} Payment Receipt</p>
                            <p>{{ \Carbon\Carbon::parse($paymentReceipt['paid_at'])->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="cl-receipt-row"><span>Receipt No.</span><span>{{ $paymentReceipt['reference'] }}</span></div>
                        <div class="cl-receipt-row"><span>Customer</span><span>{{ $paymentReceipt['customer'] }}</span></div>
                        <div class="cl-receipt-row"><span>Invoice</span><span>#{{ $paymentReceipt['invoice'] }}</span></div>
                        <div class="cl-receipt-row"><span>Invoice Date</span><span>{{ \Carbon\Carbon::parse($paymentReceipt['invoice_date'])->format('d M Y') }}</span></div>
                        <div class="cl-receipt-row"><span>Payment Method</span><span>{{ $paymentReceipt['method'] }}</span></div>
                        @if (count($paymentReceipt['items'] ?? []) > 0)
                            <div style="border-top:1px dashed #cbd5e1;margin-top:6px;padding-top:6px;">
                                <div class="cl-receipt-row" style="font-weight:700;text-transform:uppercase;font-size:10px;letter-spacing:.04em;"><span>Items</span><span></span></div>
                                @foreach ($paymentReceipt['items'] as $item)
                                    <div class="cl-receipt-row"><span>{{ $item['name'] }} × {{ $item['quantity'] }}</span><span>{{ $currency }} {{ number_format($item['line_total'], 0) }}</span></div>
                                @endforeach
                            </div>
                        @endif
                        <div class="cl-receipt-row"><span>Invoice Total</span><span>{{ $currency }} {{ number_format($paymentReceipt['total'], 0) }}</span></div>
                        <div class="cl-receipt-row"><span>Total Paid</span><span>{{ $currency }} {{ number_format($paymentReceipt['paid_total'], 0) }}</span></div>
                        <div class="cl-receipt-row is-total"><span>Amount Received</span><span>{{ $currency }} {{ number_format($paymentReceipt['amount'], 0) }}</span></div>
                        <div class="cl-receipt-row {{ $paymentReceipt['remaining'] > 0 ? '' : 'is-total' }}"><span>{{ $paymentReceipt['remaining'] > 0 ? 'Balance Remaining' : 'Balance Cleared' }}</span><span>{{ $paymentReceipt['remaining'] > 0 ? $currency . ' ' . number_format($paymentReceipt['remaining'], 0) : '—' }}</span></div>
                        <div class="cl-receipt-head" style="margin-top:10px;padding-top:8px;border-top:1px dashed #cbd5e1;border-bottom:0;padding-bottom:0;margin-bottom:0;">
                            <p>Thank you for your payment.</p>
                        </div>
                    </div>
                </div>
                <div class="cl-modal-foot">
                    <button type="button" class="cl-btn is-ghost" wire:click="$set('showPaymentReceipt', false)">Close</button>
                    <button class="cl-btn is-primary" onclick="printPaymentReceipt()">Print Receipt</button>
                </div>
            </div>
        </div>
        <script>
            function printPaymentReceipt() {
                var body = document.getElementById('payment-receipt-print');
                if (!body) return;
                var w = window.open('', '_blank', 'width=420,height=600');
                if (!w) return;
                w.document.write('<html><head><title>Payment Receipt</title></head><body style="margin:0;padding:16px;font-family:Courier New,monospace;">' + body.outerHTML + '</body></html>');
                w.document.close();
                w.focus();
                w.print();
            }
        </script>
    @endif

    @if($showSendEmailModal)
    <div style="position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" wire:click.self="closeSendEmailModal">
        <div style="background:#fff;border-radius:14px;width:92%;max-width:540px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">
                    @if($sendEmailDocType === 'statement') Send Account Statement
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
                <button type="button" wire:click="sendEmailFromModal" {{ $sendEmailSending ? 'disabled' : '' }} style="padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:{{ $sendEmailSending ? '#94a3b8' : '#2563eb' }};color:#fff;">
                    @if($sendEmailSending) Sending... @else Send Email @endif
                </button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>