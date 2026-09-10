<x-filament-panels::page>
    <style>
        .dreams-invoice-page {
            --in-navy: #1e293b;
            --in-ink: #334155;
            --in-muted: #64748b;
            --in-border: #e2e8f0;
            --in-head-bg: #f1f5f9;
            --in-white: #ffffff;
            --in-orange: #f97316;
            --in-green: #22c55e;
            --in-green-soft: #dcfce7;
            --in-red: #ef4444;
            --in-red-soft: #fee2e2;
            --in-amber: #f59e0b;
            --in-amber-soft: #fef3c7;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--in-ink);
        }

        .dreams-invoice-page * { box-sizing: border-box; }

        .dreams-invoice-page .in-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .dreams-invoice-page .in-page-title h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--in-navy);
        }

        .dreams-invoice-page .in-page-title h6 {
            margin: 4px 0 0;
            font-size: 13px;
            font-weight: 400;
            color: var(--in-muted);
        }

        .dreams-invoice-page .in-head-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dreams-invoice-page .in-head-action {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: var(--in-white);
            border: 1px solid var(--in-border);
            border-radius: 6px;
            color: var(--in-muted);
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .dreams-invoice-page .in-head-action:hover {
            background: var(--in-head-bg);
            color: var(--in-navy);
        }

        .dreams-invoice-page .in-card {
            background: var(--in-white);
            border: 1px solid var(--in-border);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .dreams-invoice-page .in-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--in-border);
        }

        .dreams-invoice-page .in-search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--in-head-bg);
            border-radius: 6px;
            padding: 0 10px;
            height: 36px;
            width: 240px;
            max-width: 100%;
        }

        .dreams-invoice-page .in-search input {
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 13px;
            color: var(--in-ink);
            width: 100%;
        }

        .dreams-invoice-page .in-search input::placeholder {
            color: var(--in-muted);
        }

        .dreams-invoice-page .in-search svg { color: var(--in-muted); }

        .dreams-invoice-page .in-filters {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dreams-invoice-page .in-select {
            position: relative;
        }

        .dreams-invoice-page .in-select select {
            appearance: none;
            -webkit-appearance: none;
            height: 36px;
            padding: 0 32px 0 12px;
            border: 1px solid var(--in-border);
            border-radius: 6px;
            background: var(--in-white);
            font-size: 13px;
            color: var(--in-ink);
            cursor: pointer;
            outline: 0;
        }

        .dreams-invoice-page .in-select::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-top-color: var(--in-muted);
        }

        .dreams-invoice-page .in-table-wrap { overflow-x: auto; }

        .dreams-invoice-page table.in-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 860px;
        }

        .dreams-invoice-page .in-table thead th {
            background: var(--in-head-bg);
            color: var(--in-navy);
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            padding: 12px 18px;
            border-bottom: 1px solid var(--in-border);
            white-space: nowrap;
        }

        .dreams-invoice-page .in-table thead th.right {
            text-align: right;
        }

        .dreams-invoice-page .in-table tbody td {
            padding: 13px 18px;
            border-bottom: 1px solid #eef1f4;
            color: var(--in-muted);
            white-space: nowrap;
        }

        .dreams-invoice-page .in-table tbody tr:last-child td { border-bottom: 0; }

        .dreams-invoice-page .in-table tbody tr:hover td { background: #f8fafc; }

        .dreams-invoice-page .in-table tbody td.right { text-align: right; }

        .dreams-invoice-page .in-inv-no {
            color: var(--in-orange);
            font-weight: 600;
            text-decoration: none;
        }

        .dreams-invoice-page .in-inv-no:hover { text-decoration: underline; }

        .dreams-invoice-page .in-customer {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dreams-invoice-page .in-avatar {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 50%;
            background: var(--in-head-bg);
            color: var(--in-navy);
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .dreams-invoice-page .in-amount { color: var(--in-ink); font-weight: 500; }

        .dreams-invoice-page .in-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .dreams-invoice-page .in-badge svg { width: 8px; height: 8px; }

        .dreams-invoice-page .in-badge.paid {
            background: var(--in-green-soft);
            color: #15803d;
        }

        .dreams-invoice-page .in-badge.unpaid {
            background: var(--in-red-soft);
            color: #b91c1c;
        }

        .dreams-invoice-page .in-badge.overdue {
            background: var(--in-amber-soft);
            color: #b45309;
        }

        .dreams-invoice-page .in-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dreams-invoice-page .in-act {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid var(--in-border);
            border-radius: 6px;
            background: var(--in-white);
            color: var(--in-muted);
            cursor: pointer;
            text-decoration: none;
            transition: all .15s ease;
        }

        .dreams-invoice-page .in-act:hover { color: var(--in-navy); border-color: #cbd5e1; }

        .dreams-invoice-page .in-act.danger:hover { color: var(--in-red); border-color: #fecaca; }

        .dreams-invoice-page .in-empty {
            padding: 46px 20px;
            text-align: center;
            color: var(--in-muted);
            font-size: 13px;
        }

        @media (max-width: 640px) {
            .dreams-invoice-page .in-toolbar { flex-direction: column; align-items: stretch; }
            .dreams-invoice-page .in-search { width: 100%; }
        }
    </style>

    <div class="dreams-invoice-page">
        <div class="in-page-header">
            <div class="in-page-title">
                <h4>Invoices</h4>
                <h6>Manage your stock invoices</h6>
            </div>
            <div class="in-head-actions">
                <a class="in-head-action" title="Refresh" href="javascript:window.location.reload();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
                </a>
            </div>
        </div>

        <div class="in-card">
            <div class="in-toolbar">
                <div class="in-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" placeholder="Search invoices..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="in-filters">
                    <div class="in-select">
                        <select wire:model.live="customerFilter">
                            <option value="">All Customers</option>
                            @foreach ($this->getCustomers() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="in-select">
                        <select wire:model.live="statusFilter">
                            <option value="">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    @if($this->canViewAllBranches())
                    <div class="in-select">
                        <select wire:model.live="branchFilter">
                            <option value="">All Branches</option>
                            @foreach ($this->getBranches() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <div class="in-table-wrap">
                <table class="in-table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Branch</th>
                            <th>Invoice Date</th>
                            <th class="right">Amount</th>
                            <th class="right">Paid</th>
                            <th class="right">Amount Due</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->getInvoices() as $tx)
                            @php
                                $status = $this->invoiceStatus($tx);
                                $badge = strtolower($status);
                            @endphp
                            <tr>
                                <td>
                                    <a class="in-inv-no" href="{{ $this->getInvoiceUrl($tx) }}">{{ $tx->receipt_number ?? $tx->id }}</a>
                                </td>
                                <td>
                                    <div class="in-customer">
                                        <span class="in-avatar">{{ strtoupper(substr((string) ($tx->customer_name ?? 'C'), 0, 1)) }}</span>
                                        <span>{{ $tx->customer_name ?? 'Walk-in Customer' }}</span>
                                    </div>
                                </td>
                                <td>{{ $tx->branch_name ?? '—' }}</td>
                                <td>{{ optional($tx->transaction_date)->format('M d, Y') ?? '-' }}</td>
                                <td class="right in-amount">{{ number_format((float) $tx->total_amount, 2) }}</td>
                                <td class="right">{{ number_format((float) $tx->amount_paid, 2) }}</td>
                                <td class="right">{{ number_format((float) ($tx->balance_due ?? 0), 2) }}</td>
                                <td>
                                    <span class="in-badge {{ $badge }}">
                                        <svg viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="in-actions">
                                        <a class="in-act" title="View" href="{{ $this->getInvoiceUrl($tx) }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button class="in-act danger" title="Delete" wire:click="deleteInvoice('{{ $tx->id }}')" wire:confirm="Are you sure you want to delete this invoice?">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="in-empty">No invoices found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>