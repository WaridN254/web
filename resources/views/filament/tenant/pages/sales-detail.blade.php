<div class="dreams-detail-popup">
    <style>
        .dreams-detail-popup,
        .dreams-detail-popup * {
            box-sizing: border-box;
        }

        .dreams-detail-popup {
            --dd-navy: #1e293b;
            --dd-ink: #334155;
            --dd-muted: #64748b;
            --dd-border: #e2e8f0;
            --dd-head-bg: #e5e9ec;
            --dd-panel-bg: #f1f5f9;
            --dd-orange: #f97316;
            --dd-green: #22c55e;
            --dd-green-pale: #dcfce7;
            --dd-shadow: 0 1px 2px rgba(15, 23, 42, .06);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--dd-ink);
            background: #ffffff;
            padding: 24px 28px 20px;
        }

        .dreams-detail-popup .dd-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin: -6px 0 18px;
        }

        .dreams-detail-popup .dd-icon-btn {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: #ffffff;
            border: 1px solid var(--dd-border);
            border-radius: 6px;
            cursor: pointer;
            color: var(--dd-muted);
            transition: background .15s ease, color .15s ease;
        }

        .dreams-detail-popup .dd-icon-btn:hover {
            background: var(--dd-panel-bg);
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-icon-btn.pdf svg {
            color: #ef4444;
        }

        .dreams-detail-popup .dd-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            padding: 4px 0 24px;
            border-bottom: 1px solid var(--dd-border);
        }

        .dreams-detail-popup .dd-col h2 {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-name {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 600;
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-meta {
            font-size: 13px;
            line-height: 1.7;
            color: var(--dd-muted);
        }

        .dreams-detail-popup .dd-meta a {
            color: inherit;
            text-decoration: none;
        }

        .dreams-detail-popup .dd-meta a:hover {
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-meta strong {
            font-weight: 600;
            color: var(--dd-ink);
        }

        .dreams-detail-popup .dd-invoice-list {
            display: flex;
            flex-direction: column;
            gap: 7px;
            font-size: 13px;
            line-height: 1.6;
            color: var(--dd-muted);
        }

        .dreams-detail-popup .dd-invoice-list .ref {
            color: var(--dd-orange);
            font-weight: 600;
        }

        .dreams-detail-popup .dd-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .dreams-detail-popup .dd-badge.green {
            background: var(--dd-green);
            color: #ffffff;
        }

        .dreams-detail-popup .dd-badge.pale-green {
            background: var(--dd-green-pale);
            color: #15803d;
        }

        .dreams-detail-popup .dd-summary {
            padding-top: 24px;
        }

        .dreams-detail-popup .dd-summary h2 {
            margin: 0 0 14px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--dd-border);
            border-radius: 6px;
        }

        .dreams-detail-popup table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 720px;
        }

        .dreams-detail-popup thead th {
            background: var(--dd-head-bg);
            color: var(--dd-navy);
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            padding: 11px 14px;
            border-bottom: 1px solid var(--dd-border);
            white-space: nowrap;
        }

        .dreams-detail-popup thead th:not(:first-child) {
            text-align: right;
        }

        .dreams-detail-popup tbody td {
            padding: 9px 14px;
            border-bottom: 1px solid #eef1f4;
            color: var(--dd-muted);
            white-space: nowrap;
        }

        .dreams-detail-popup tbody tr:last-child td {
            border-bottom: 0;
        }

        .dreams-detail-popup tbody td:not(:first-child) {
            text-align: right;
        }

        .dreams-detail-popup .dd-prod {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dreams-detail-popup .dd-prod-thumb {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 6px;
            background: var(--dd-panel-bg);
            display: grid;
            place-items: center;
            overflow: hidden;
            font-size: 11px;
            font-weight: 600;
            color: var(--dd-muted);
            text-transform: uppercase;
        }

        .dreams-detail-popup .dd-prod-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dreams-detail-popup .dd-prod-name {
            font-weight: 500;
            color: var(--dd-navy);
        }

        .dreams-detail-popup .dd-totals {
            margin: 20px 0 0 auto;
            width: 440px;
            max-width: 100%;
            border: 1px solid var(--dd-border);
            border-radius: 6px;
            overflow: hidden;
        }

        .dreams-detail-popup .dd-totals table {
            min-width: 0;
        }

        .dreams-detail-popup .dd-totals td {
            padding: 9px 16px;
            height: 36px;
        }

        .dreams-detail-popup .dd-totals td:first-child {
            width: 55%;
            background: var(--dd-panel-bg);
            color: var(--dd-ink);
            font-weight: 500;
            border-right: 1px solid var(--dd-border);
        }

        .dreams-detail-popup .dd-totals td:last-child {
            text-align: right;
            color: var(--dd-ink);
            font-weight: 500;
            background: #ffffff;
        }

        .dreams-detail-popup .dd-totals tr + tr td {
            border-top: 1px solid #eef1f4;
        }

        @media (max-width: 800px) {
            .dreams-detail-popup .dd-info {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .dreams-detail-popup .dd-totals {
                width: 100%;
            }
        }
    </style>

    <div class="dd-actions">
        <button type="button" class="dd-icon-btn pdf" aria-label="Download PDF" title="Download PDF">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>
        </button>
        <button type="button" class="dd-icon-btn" aria-label="Print" title="Print" x-on:click="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/></svg>
        </button>
    </div>

    <div class="dd-info">
        <div class="dd-col">
            <h2>Customer Info</h2>
            <p class="dd-name">{{ $customer?->full_name ?? ($sale->customer_name ?? 'Walk-in Customer') }}</p>
            <div class="dd-meta">
                @if ($customer?->address)
                    <div>{{ $customer->address }}</div>
                @endif
                @if ($customer?->email)
                    <div>Email <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></div>
                @endif
                @if ($customer?->phone)
                    <div>Phone {{ $customer->phone }}</div>
                @endif
            </div>
        </div>

        <div class="dd-col">
            <h2>Company Info</h2>
            <p class="dd-name">{{ $companyName ?? 'HALIS' }}</p>
            <div class="dd-meta">
                @if ($companyAddress ?? null)
                    <div>{{ $companyAddress }}</div>
                @endif
                @if ($companyEmail ?? null)
                    <div>Email <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a></div>
                @endif
                @if ($companyPhone ?? null)
                    <div>Phone {{ $companyPhone }}</div>
                @endif
            </div>
        </div>

        <div class="dd-col">
            <h2>Invoice Info</h2>
            <div class="dd-invoice-list">
                <div><strong>Reference:</strong> <span class="ref">#{{ $sale->receipt_number ?? 'N/A' }}</span></div>
                <div><strong>Date:</strong> {{ optional($sale->transaction_date)->format('M d, Y') ?? '-' }}</div>
                @if ($branch_name ?? null)
                    <div><strong>Branch:</strong> {{ $branch_name }}</div>
                @endif
                <div><strong>Operated By:</strong> {{ $cashier?->full_name ?? '—' }}</div>
                <div>
                    <strong>Status:</strong>
                    <span class="dd-badge green">{{ ucfirst((string) ($sale->status ?? 'Completed')) }}</span>
                </div>
                <div>
                    <strong>Payment Status:</strong>
                    <span class="dd-badge pale-green">{{ ((float) ($sale->balance_due ?? 0)) > 0 ? 'Partial' : 'Paid' }}</span>
                </div>
                @if ($sale->register_name ?? null)
                    <div><strong>Register:</strong> {{ $sale->register_name }}</div>
                @endif
                @if ($sale->terminal_id ?? null)
                    <div><strong>Terminal:</strong> {{ $sale->terminal_id }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="dd-summary">
        <h2>Order Summary</h2>
        <div class="dd-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Purchase Price({{ $currencyCode }})</th>
                        <th>Discount({{ $currencyCode }})</th>
                        <th>Tax(%)</th>
                        <th>Tax Amount({{ $currencyCode }})</th>
                        <th>Unit Cost({{ $currencyCode }})</th>
                        <th>Total Cost({{ $currencyCode }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <div class="dd-prod">
                                    <span class="dd-prod-thumb">{{ strtoupper(substr((string) ($item->product_name ?? 'P'), 0, 1)) }}</span>
                                    <span class="dd-prod-name">{{ $item->product_name ?? 'Item' }}</span>
                                </div>
                            </td>
                            <td>{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($item->discount_applied ?? $item->discount ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($item->tax_rate ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($item->tax_amount ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($item->cost_price ?? $item->unit_price ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($item->line_total ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--dd-muted);">No items found for this sale.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="dd-totals">
        <table>
            <tr>
                <td>Order Tax</td>
                <td>{{ $currencyCode }} {{ number_format((float) ($sale->tax_amount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ $currencyCode }} {{ number_format((float) ($sale->discount_amount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td>{{ $currencyCode }} {{ number_format((float) ($sale->total_amount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td>{{ $currencyCode }} {{ number_format((float) ($sale->amount_paid ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Due</td>
                <td>{{ $currencyCode }} {{ number_format((float) ($sale->balance_due ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>

