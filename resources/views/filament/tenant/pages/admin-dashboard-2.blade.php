<x-filament-panels::page>
    @php
        $d = $this->data();
        $money = fn ($amount) => number_format((float) $amount, 0, '.', ',');
        $P = [
            'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            'banknotes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
            'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
            'calculator' => '<rect x="4" y="2" width="16" height="20" rx="2"/><rect x="7" y="5" width="10" height="4" rx="1"/><path d="M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01M16 17h.01"/>',
            'truck' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
            'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
            'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
            'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        ];
        $icon = function ($name, $size = 20) use ($P) {
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $P[$name] . '</svg>';
        };
    @endphp

    <style>
        .db2-grid { display: grid; gap: 16px; }
        .db2-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .db2-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .db2-card {
            background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 18px;
        }
        .db2-stat {
            background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 18px;
            display: flex; align-items: center; gap: 14px; min-width: 0;
        }
        .db2-stat-icon {
            width: 46px; height: 46px; flex: 0 0 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .db2-stat-label { margin: 0; font-size: 12px; font-weight: 600; color: #6B7280; }
        .db2-stat-value { margin: 4px 0 0; font-size: 20px; font-weight: 800; color: #1F2937; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
        .db2-card-title { margin: 0; font-size: 14px; font-weight: 700; color: #1F2937; }
        .db2-table { width: 100%; border-collapse: collapse; }
        .db2-table th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #6B7280; font-weight: 600; padding: 8px 10px; border-bottom: 1px solid #E9EDF2; white-space: nowrap; }
        .db2-table td { padding: 10px; border-bottom: 1px solid #F3F4F6; font-size: 13px; color: #1F2937; vertical-align: middle; }
        .db2-table tr:last-child td { border-bottom: none; }
        .db2-avatar { width: 36px; height: 36px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0; }
        .db2-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        @media (max-width: 1120px) { .db2-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .db2-grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .db2-grid-4 { grid-template-columns: 1fr; } }
    </style>

    <div class="space-y-6">
        {{-- Finance cards --}}
        <div class="db2-grid db2-grid-4">
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#E6F7E9; color:#198754;">{!! $icon('banknotes') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Total Purchase Due</p>
                    <p class="db2-stat-value">{{ $d['currency'] }} {{ $money($d['purchaseDue']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#E0F7FA; color:#0891b2;">{!! $icon('calculator') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Total Sales Due</p>
                    <p class="db2-stat-value">{{ $d['currency'] }} {{ $money($d['salesDue']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#FFF4E6; color:#E04F16;">{!! $icon('wallet') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Total Sale Amount</p>
                    <p class="db2-stat-value">{{ $d['currency'] }} {{ $money($d['totalSaleAmount']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#FFE6E6; color:#DC3545;">{!! $icon('clock') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Total Expense Amount</p>
                    <p class="db2-stat-value">{{ $d['currency'] }} {{ $money($d['totalExpenses']) }}</p>
                </div>
            </div>
        </div>

        {{-- Count cards --}}
        <div class="db2-grid db2-grid-4">
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#E0F7FA; color:#0DCAF0;">{!! $icon('users') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Customers</p>
                    <p class="db2-stat-value">{{ number_format($d['customers']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#FCE7F3; color:#BE185D;">{!! $icon('truck') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Suppliers</p>
                    <p class="db2-stat-value">{{ number_format($d['suppliers']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#E0F2EF; color:#0E9384;">{!! $icon('bag') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Purchase Invoices</p>
                    <p class="db2-stat-value">{{ number_format($d['purchaseInvoices']) }}</p>
                </div>
            </div>
            <div class="db2-stat">
                <div class="db2-stat-icon" style="background:#EAF2FE; color:#0D6EFD;">{!! $icon('document-text') !!}</div>
                <div style="min-width:0;">
                    <p class="db2-stat-label">Sales Invoices</p>
                    <p class="db2-stat-value">{{ number_format($d['salesInvoices']) }}</p>
                </div>
            </div>
        </div>

        {{-- Purchase & Sales chart --}}
        <div class="db2-card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="width:34px; height:34px; border-radius:8px; background:#EAF2FE; color:#0D6EFD; display:flex; align-items:center; justify-content:center;">{!! $icon('chart-bar', 18) !!}</span>
                    <div>
                        <h4 class="db2-card-title">Purchase & Sales</h4>
                        <p style="margin:2px 0 0; font-size:11px; color:#6B7280;">Monthly comparison for {{ now()->year }}</p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:14px; font-size:12px; color:#6B7280;">
                    <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#0D6EFD;"></span>Sales</span>
                    <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#FE9F43;"></span>Purchase</span>
                </div>
            </div>
            <div style="display:flex; gap:6px; height:220px; align-items:stretch;">
                @foreach ($d['chartLabels'] as $i => $label)
                    @php
                        $sh = round(($d['salesChart'][$i] / $d['maxChart']) * 100);
                        $ph = round(($d['purchaseChart'][$i] / $d['maxChart']) * 100);
                    @endphp
                    <div style="flex:1; position:relative; display:flex; flex-direction:column; justify-content:flex-end; gap:3px; height:100%;" title="{{ $label }} &mdash; Sales {{ $d['currency'] }} {{ $money($d['salesChart'][$i]) }}, Purchase {{ $d['currency'] }} {{ $money($d['purchaseChart'][$i]) }}">
                        @if ($sh > 0)<div style="height: {{ $sh }}%; background: #0D6EFD; border-radius: 3px 3px 0 0;"></div>@endif
                        @if ($ph > 0)<div style="height: {{ $ph }}%; background: #FE9F43; border-radius: 3px 3px 0 0;"></div>@endif
                    </div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                @foreach ($d['chartLabels'] as $l)
                    <span style="font-size:11px; color:#9CA3AF;">{{ $l }}</span>
                @endforeach
            </div>
        </div>

        {{-- Recently added + expired products --}}
        <div class="db2-grid db2-grid-2">
            <div class="db2-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 class="db2-card-title">Recently Added Products</h4>
                    <a href="/tenant/products" style="font-size:12px; color:#0D6EFD; text-decoration:underline; font-weight:500;">View All</a>
                </div>
                <table class="db2-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Products</th>
                            <th style="text-align:right;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($d['recentProducts'] as $i => $p)
                            <tr>
                                <td style="color:#6B7280;">{{ $i + 1 }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span class="db2-avatar" style="background:{{ ['#FE9F43','#0E9384','#4B49AC','#E04F16','#6F42C1'][$i % 5] }};">
                                            @if (! empty($p->image))
                                                <img src="{{ $p->image }}" alt="{{ $p->name }}" />
                                            @else
                                                {{ strtoupper(mb_substr($p->name, 0, 1)) }}
                                            @endif
                                        </span>
                                        <span style="font-weight:600; color:#1F2937;">{{ $p->name }}</span>
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700;">{{ $d['currency'] }} {{ $money($p->price) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:#6B7280; padding:20px 0;">No products yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="db2-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h4 class="db2-card-title">Expired Products</h4>
                    <a href="/tenant/product-expiry-report" style="font-size:12px; color:#0D6EFD; text-decoration:underline; font-weight:500;">View All</a>
                </div>
                <table class="db2-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Expired Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($d['expiredProducts'] as $p)
                            <tr>
                                <td style="font-weight:600;">{{ $p->name }}</td>
                                <td style="color:#6B7280;">#{{ $p->barcode ?: substr((string) $p->id, 0, 6) }}</td>
                                <td style="color:#DC3545;">{{ \Carbon\Carbon::parse($p->expiration_date)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:#6B7280; padding:20px 0;">No expired products</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>