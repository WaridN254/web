<x-filament-panels::page>
    <style>
        .sp-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            min-width: 0;
        }

        .sp-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            border: 1px solid var(--line, #e5e7eb);
            border-radius: 12px;
            background: var(--panel, #ffffff);
            padding: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .sp-stat-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: grid;
            place-items: center;
            border-radius: 10px;
        }

        .sp-stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .sp-stat.is-orange .sp-stat-icon { background: #fff3e6; color: #f97316; }
        .sp-stat.is-red .sp-stat-icon { background: #fee2e2; color: #dc2626; }
        .sp-stat.is-green .sp-stat-icon { background: #ecfdf5; color: #059669; }
        .sp-stat.is-purple .sp-stat-icon { background: #f5f3ff; color: #7c3aed; }

        .sp-stat-label {
            margin: 0;
            font-size: 11px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted, #64748b);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sp-stat-value {
            margin: 4px 0 0;
            font-size: 20px;
            font-weight: 850;
            letter-spacing: -0.02em;
            color: var(--text, #0f172a);
            font-variant-numeric: tabular-nums;
        }

        .sp-stat.is-red .sp-stat-value { color: #dc2626; }
        .sp-stat.is-green .sp-stat-value { color: #059669; }
        .sp-stat.is-purple .sp-stat-value { color: #7c3aed; }

        .sp-stat-title {
            min-width: 0;
            color: var(--text, #0f172a);
            font-size: 15px;
            font-weight: 850;
            letter-spacing: -0.02em;
        }

        @media (max-width: 1120px) {
            .sp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .sp-stats { grid-template-columns: 1fr; }
        }
    </style>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sales History</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all recorded sales transactions</p>
            </div>
        </div>

        @php $stats = $this->stats(); $currency = $this->currency(); @endphp

        <div class="sp-stats">
            <div class="sp-stat is-orange">
                <div class="sp-stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                </div>
                <div style="min-width:0;">
                    <p class="sp-stat-label">Today's Sales</p>
                    <p class="sp-stat-value">{{ $currency }} {{ number_format($stats['todaySales'], 0) }}</p>
                </div>
            </div>

            <div class="sp-stat is-red">
                <div class="sp-stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div style="min-width:0;">
                    <p class="sp-stat-label">Amount Pending</p>
                    <p class="sp-stat-value">{{ $currency }} {{ number_format($stats['pendingAmount'], 0) }}</p>
                </div>
            </div>

            <div class="sp-stat is-green">
                <div class="sp-stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                </div>
                <div style="min-width:0;">
                    <p class="sp-stat-label">Pending Orders</p>
                    <p class="sp-stat-value">{{ number_format($stats['pendingOrders'], 0) }}</p>
                </div>
            </div>

            <div class="sp-stat is-purple">
                <div class="sp-stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </div>
                <div style="min-width:0;">
                    <p class="sp-stat-label">Active Layaway</p>
                    <p class="sp-stat-value">{{ number_format($stats['activePlans'], 0) }} plans</p>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>

    @if ($showRefundModal)
        <div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);" wire:click.self="$set('showRefundModal', false)">
            <div style="background:#fff;border-radius:16px;width:90%;max-width:820px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.3);" wire:key="refund-modal">

                {{-- Header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e5e7eb;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:grid;place-items:center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 0 1 0 8h-1"/></svg>
                        </div>
                        <div>
                            <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Process Refund</h3>
                            <p style="margin:2px 0 0;font-size:12px;color:#64748b;">Original: {{ $refundSaleData['receipt_number'] ?? '—' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showRefundModal', false)" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;padding:4px 8px;border-radius:6px;" onmouseover="this.style.color='#475569'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                </div>

                {{-- Body --}}
                <div style="display:flex;flex:1;overflow:hidden;min-height:0;">

                    {{-- Left: Items --}}
                    <div style="flex:1;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;min-height:0;">
                        {{-- Select all --}}
                        <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:#334155;">
                                <input type="checkbox" wire:click="toggleRefundSelectAll" @if($refundSelectAll) checked @endif style="width:16px;height:16px;accent-color:#ea580c;" />
                                Select / Unselect all
                            </label>
                        </div>

                        {{-- Items list --}}
                        <div style="flex:1;overflow-y:auto;padding:0;">
                            @foreach($refundItems as $idx => $item)
                                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f1f5f9;{{ $item['selected'] ? 'background:#fffbeb;' : 'background:#fafafa;' }}">
                                    <input type="checkbox" wire:click="toggleRefundItem({{ $idx }})" @if($item['selected']) checked @endif style="width:16px;height:16px;accent-color:#ea580c;flex-shrink:0;" />
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $item['product_name'] }}</div>
                                        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ number_format($item['quantity'], 0) }} × {{ number_format($item['unit_price'], 0) }}</div>
                                    </div>
                                    <div style="font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap;">{{ number_format($item['line_total'], 0) }}</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total --}}
                        <div style="padding:14px 16px;border-top:2px solid #e5e7eb;background:#0f172a;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Total Refund Amount</span>
                            <span style="font-size:20px;font-weight:800;color:#f97316;">-{{ $currency }} {{ number_format($refundTotal, 0) }}</span>
                        </div>
                    </div>

                    {{-- Right: Settings --}}
                    <div style="width:340px;display:flex;flex-direction:column;gap:0;overflow-y:auto;">

                        {{-- Receipt number --}}
                        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                            <label style="display:block;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Enter receipt number and payment type to confirm refund</label>
                            <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Receipt number</label>
                            <input type="text" wire:model="refundReceiptNumber" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-weight:600;color:#0f172a;background:#f8fafc;" />
                        </div>

                        {{-- Payment type --}}
                        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                            <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:8px;">Refund payment type</label>
                            <div style="display:flex;gap:8px;">
                                @php
                                    $types = [
                                        'cash' => ['label' => 'CASH', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>'],
                                        'card' => ['label' => 'CARD', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>'],
                                        'check' => ['label' => 'CHECK', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>'],
                                    ];
                                @endphp
                                @foreach($types as $key => $type)
                                    <button type="button"
                                        wire:click="setRefundPaymentType('{{ $key }}')"
                                        style="flex:1;padding:12px 8px;border-radius:10px;border:2px solid {{ $refundPaymentType === $key ? '#ea580c' : '#e2e8f0' }};background:{{ $refundPaymentType === $key ? '#fff7ed' : '#fff' }};color:{{ $refundPaymentType === $key ? '#ea580c' : '#64748b' }};font-size:12px;font-weight:700;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:6px;transition:all 0.15s;">
                                        {!! $type['icon'] !!}
                                        {{ $type['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Stock return condition --}}
                        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                            <label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:10px;">Stock return condition</label>
                            @foreach($refundItems as $idx => $item)
                                @if($item['selected'])
                                    <div style="margin-bottom:10px;">
                                        <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">{{ $item['product_name'] }}</div>
                                        <div style="display:flex;gap:6px;">
                                            @php
                                                $conditions = [
                                                    'resalable' => ['label' => 'Resalable', 'color' => '#16a34a', 'bg' => '#dcfce7', 'border' => '#86efac'],
                                                    'damaged' => ['label' => 'Damaged', 'color' => '#d97706', 'bg' => '#fef3c7', 'border' => '#fcd34d'],
                                                    'not_resalable' => ['label' => 'Not resalable', 'color' => '#dc2626', 'bg' => '#fee2e2', 'border' => '#fca5a5'],
                                                ];
                                            @endphp
                                            @foreach($conditions as $condKey => $cond)
                                                <button type="button"
                                                    wire:click="setRefundCondition({{ $idx }}, '{{ $condKey }}')"
                                                    style="padding:5px 10px;border-radius:6px;border:1px solid {{ $item['condition'] === $condKey ? $cond['border'] : '#e2e8f0' }};background:{{ $item['condition'] === $condKey ? $cond['bg'] : '#fff' }};color:{{ $item['condition'] === $condKey ? $cond['color'] : '#94a3b8' }};font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;transition:all 0.15s;">
                                                    @if($item['condition'] === $condKey)✓ @endif {{ $cond['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Reason --}}
                        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                            <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Reason</label>
                            <textarea wire:model="refundReason" rows="2" placeholder="Reason for refund..." style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;resize:none;"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div style="display:flex;gap:18px;justify-content:flex-end;padding:16px 24px;border-top:1px solid #e5e7eb;background:#f8fafc;border-radius:0 0 16px 16px;">
                    <button type="button" wire:click="$set('showRefundModal', false)" style="padding:10px 24px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
                    <button type="button" wire:click="processRefund" onclick="return confirm('Process this refund?')" style="padding:10px 28px;border-radius:10px;border:none;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/></svg>
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showReceiptModal && $receiptData)
        <style>
            .sp-receipt-overlay { position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px); }
            .sp-receipt-modal { background:#fff;border-radius:14px;width:92%;max-width:430px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden; }
            .sp-receipt-modal-head { display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e2e8f0;background:#fff; }
            .sp-receipt-modal-head h3 { margin:0;font-size:15px;font-weight:700;color:#0f172a; }
            .sp-receipt-modal-close { background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;padding:2px 6px;border-radius:6px; }
            .sp-receipt-modal-close:hover { color:#475569; }
            .sp-receipt-modal-body { flex:1;overflow-y:auto;background:#f1f5f9;padding:16px; }
            .sp-receipt-wrap { width:100%;max-width:400px;margin:0 auto;padding:18px 16px;background:#fff;color:#1e293b;font-family:'Courier New',Courier,monospace; }
            .sp-receipt-center { text-align:center; }
            .sp-receipt-wrap h2 { margin:0;color:#0f172a;font-size:17px;font-weight:850;letter-spacing:.03em; }
            .sp-receipt-wrap p { margin:2px 0;font-size:11px;line-height:1.5;color:#334155; }
            .sp-receipt-wrap hr { margin:8px 0;border:0;border-top:1px dashed #cbd5e1; }
            .sp-receipt-tbl { width:100%;border-collapse:collapse;font-size:11px; }
            .sp-receipt-tbl th { padding:3px 0;text-align:left;border-bottom:1px dashed #cbd5e1;color:#0f172a;font-weight:800; }
            .sp-receipt-tbl th:nth-child(3),.sp-receipt-tbl td:nth-child(3) { text-align:right; }
            .sp-receipt-tbl td { padding:3px 0;vertical-align:top; }
            .sp-receipt-line { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:2px 0;font-size:11.5px; }
            .sp-receipt-line strong { color:#0f172a; }
            .sp-receipt-grand { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:6px 0;border-top:1px solid #0f172a;border-bottom:1px solid #0f172a;font-size:13px;font-weight:850;color:#0f172a; }
            .sp-receipt-footer { margin-top:8px;font-size:10.5px;line-height:1.6;color:#475569; }
            .sp-receipt-badge { display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700; }
            .sp-receipt-badge-red { background:#fee2e2;color:#dc2626; }
            .sp-receipt-badge-blue { background:#dbeafe;color:#1d4ed8; }
            .sp-receipt-actions { display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:12px 18px;border-top:1px solid #e2e8f0;background:#fff; }
            .sp-receipt-btn { padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.15s; }
            .sp-receipt-btn-done { background:#fff;color:#475569;border:1px solid #e2e8f0; }
            .sp-receipt-btn-done:hover { background:#f8fafc; }
            .sp-receipt-btn-print { background:#2563eb;color:#fff; }
            .sp-receipt-btn-print:hover { background:#1d4ed8; }
        </style>

        <div class="sp-receipt-overlay" wire:click.self="$set('showReceiptModal', false)">
            <div class="sp-receipt-modal" wire:key="receipt-modal">
                <div class="sp-receipt-modal-head">
                    <h3>{{ $receiptType === 'refund' ? 'Refund Receipt' : 'Payment Receipt' }}</h3>
                    <button type="button" class="sp-receipt-modal-close" wire:click="closeReceiptModal">&times;</button>
                </div>
                <div class="sp-receipt-modal-body">
                    <div class="sp-receipt-wrap" id="sales-page-receipt-printable">
                        <div class="sp-receipt-center">
                            <h2>{{ $receiptData['company']['name'] }}</h2>
                            @if (!empty($receiptData['company']['address']))
                                <p>{{ $receiptData['company']['address'] }}</p>
                            @endif
                            @if (!empty($receiptData['company']['phone']))
                                <p>Tel: {{ $receiptData['company']['phone'] }}</p>
                            @endif
                            @if (!empty($receiptData['company']['tin']))
                                <p>TIN: {{ $receiptData['company']['tin'] }}</p>
                            @endif
                        </div>

                        <hr>

                        <h2 style="font-size:14px;margin:6px 0 4px;">{{ $receiptType === 'refund' ? 'REFUND RECEIPT' : 'PAYMENT RECEIPT' }}</h2>

                        <p><strong>Receipt:</strong> {{ $receiptData['reference'] }}</p>
                        <p><strong>Date:</strong> {{ $receiptData['date'] }}</p>
                        <p><strong>Branch:</strong> {{ $receiptData['branch'] ?? '—' }}</p>
                        <p><strong>Cashier:</strong> {{ $receiptData['cashier'] }}</p>
                        <p><strong>Customer:</strong> {{ $receiptData['customer'] }}</p>

                        @if ($receiptType === 'refund')
                            <p><strong>Original Receipt:</strong> {{ $receiptData['original_receipt'] ?? '—' }}</p>
                            <p><strong>Payment Type:</strong> <span class="sp-receipt-badge sp-receipt-badge-red">{{ strtoupper($receiptData['payment_type'] ?? '') }}</span></p>
                        @else
                            <p><strong>Invoice:</strong> #{{ $receiptData['invoice'] ?? '—' }}</p>
                            <p><strong>Invoice Date:</strong> {{ $receiptData['invoice_date'] ?? '—' }}</p>
                            <p><strong>Payment Method:</strong> <span class="sp-receipt-badge sp-receipt-badge-blue">{{ strtoupper($receiptData['payment_method'] ?? '') }}</span></p>
                        @endif

                        <hr>

                        <table class="sp-receipt-tbl">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align:center">Qty</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($receiptData['items'] as $item)
                                    <tr>
                                        <td>{{ $receiptType === 'refund' ? ($item['product_name'] ?? '') : (($item['name'] ?? '') . ' x ' . ($item['quantity'] ?? 0)) }}</td>
                                        @if ($receiptType === 'refund')
                                            <td style="text-align:center">{{ number_format((float) ($item['quantity'] ?? 0), 0) }}</td>
                                            <td>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($item['line_total'] ?? 0), 0) }}</td>
                                        @else
                                            <td></td>
                                            <td>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($item['line_total'] ?? 0), 0) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <hr>

                        @if ($receiptType === 'refund')
                            <div class="sp-receipt-grand">
                                <span>Refund Total</span>
                                <strong>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($receiptData['total'] ?? 0), 0) }}</strong>
                            </div>
                        @else
                            <div class="sp-receipt-line">
                                <span>Invoice Total</span>
                                <strong>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($receiptData['total'] ?? 0), 0) }}</strong>
                            </div>
                            <div class="sp-receipt-line">
                                <span>Total Paid</span>
                                <strong>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($receiptData['paid_total'] ?? 0), 0) }}</strong>
                            </div>
                            <div class="sp-receipt-grand">
                                <span>Amount Received</span>
                                <strong>{{ $receiptData['company']['currency'] }} {{ number_format((float) ($receiptData['amount'] ?? 0), 0) }}</strong>
                            </div>
                            @if (($receiptData['remaining'] ?? 0) > 0)
                                <div class="sp-receipt-line" style="color:#b91c1c;font-weight:700;">
                                    <span>Balance Remaining</span>
                                    <strong>{{ $receiptData['company']['currency'] }} {{ number_format((float) $receiptData['remaining'], 0) }}</strong>
                                </div>
                            @endif
                        @endif

                        <hr>

                        <div class="sp-receipt-footer sp-receipt-center">
                            <p>Thank you for your {{ $receiptType === 'refund' ? 'purchase' : 'payment' }}.</p>
                            <p>This is a computer generated receipt.</p>
                        </div>
                    </div>
                </div>
                <div class="sp-receipt-actions">
                    <button type="button" class="sp-receipt-btn sp-receipt-btn-done" wire:click="closeReceiptModal">Done</button>
                    <button type="button" class="sp-receipt-btn sp-receipt-btn-send" wire:click="openSendEmailModal('receipt', '{{ $receiptData['reference'] ?? '' }}')" style="padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;background:#7c3aed;color:#fff;">Send by Email</button>
                    <button type="button" class="sp-receipt-btn sp-receipt-btn-print" onclick="printSalesPageReceipt()">Print Receipt</button>
                </div>
            </div>
        </div>

        <script>
            function printSalesPageReceipt() {
                var body = document.getElementById('sales-page-receipt-printable');
                if (!body) return;
                var w = window.open('', '_blank', 'width=400,height=600');
                if (!w) return;
                w.document.write('<html><head><title>Receipt</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Courier New,monospace;padding:16px;font-size:12px;color:#000}</style></head><body>' + body.outerHTML + '</body></html>');
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