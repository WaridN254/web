<x-filament-panels::page>
    @php
        $d = $this->data();
        $money = fn ($amount) => number_format((float) $amount, 0, '.', ',');
        $P = [
            'banknotes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
            'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
            'truck' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
            'trophy' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/>',
            'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
            'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            'store' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>',
        ];
        $icon = function ($name, $size = 20) use ($P) {
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $P[$name] . '</svg>';
        };
        $badge = function ($s) {
            $map = [
                'completed' => ['#E6F7E9', '#198754'], 'paid' => ['#E6F7E9', '#198754'],
                'success' => ['#E6F7E9', '#198754'],
                'pending' => ['#E0F7FA', '#0E7490'], 'resumed' => ['#E0F7FA', '#0E7490'],
                'cancelled' => ['#FFE6E6', '#DC3545'], 'refunded' => ['#FFE6E6', '#DC3545'],
                'failed' => ['#FFE6E6', '#DC3545'],
            ];
            return $map[strtolower((string) $s)] ?? ['#EEF1F5', '#6C757D'];
        };
        $badgeLabel = fn ($s) => ucfirst(strtolower((string) $s));
        $avatarColors = ['#FE9F43', '#0E9384', '#4B49AC', '#E04F16', '#6F42C1'];
        $initial = fn ($name) => strtoupper(mb_substr((string) $name, 0, 1));
        $catColors = ['#FE9F43', '#E04F16', '#092C4C', '#0E9384'];
        $catSum = max(1, (float) $d['topCategories']->sum('qty'));
        $donutGradient = '';
        $acc = 0;
        foreach ($d['topCategories'] as $i => $cat) {
            $pct = ((float) $cat->qty / $catSum) * 360;
            $donutGradient .= $catColors[$i % 4] . ' ' . round($acc, 1) . 'deg ' . round($acc + $pct, 1) . 'deg, ';
            $acc += $pct;
        }
        $donutGradient = $donutGradient ? rtrim($donutGradient, ', ') : '#E9EDF2 0 360deg';
    @endphp

    <style>
        .sd-grid { display: grid; gap: 20px; }
        .sd-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sd-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sd-card { background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 18px; }
        .sd-stat { background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 18px; min-width: 0; }
        .sd-stat-icon { width: 46px; height: 46px; flex: 0 0 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .sd-stat-label { margin: 0; font-size: 12px; font-weight: 600; color: #6B7280; }
        .sd-stat-value { margin: 4px 0 0; font-size: 22px; font-weight: 800; color: #1F2937; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
        .sd-card-title { margin: 0; font-size: 14px; font-weight: 700; color: #1F2937; }
        .sd-avatar { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: #fff; flex-shrink: 0; }
        .sd-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sd-table { width: 100%; border-collapse: collapse; }
        .sd-table th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #6B7280; font-weight: 600; padding: 8px 10px; border-bottom: 1px solid #E9EDF2; white-space: nowrap; }
        .sd-table td { padding: 10px; border-bottom: 1px solid #F3F4F6; font-size: 13px; color: #1F2937; vertical-align: middle; }
        .sd-table tr:last-child td { border-bottom: none; }
        @media (max-width: 1120px) { .sd-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .sd-grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .sd-grid-3 { grid-template-columns: 1fr; } }
    </style>

    <div class="space-y-8">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="width:44px; height:44px; border-radius:50%; background:#FE9F43; color:#fff; display:flex; align-items:center; justify-content:center; font-size:17px; font-weight:800;">{{ strtoupper(mb_substr(($d['userName'] ?: 'U'), 0, 1)) }}</span>
                <div>
                    <h1 style="margin:0; font-size:20px; font-weight:800; color:#1F2937;">👋 Hi {{ $d['userName'] }}, <span style="font-weight:400; font-size:15px; color:#6B7280;">here's what's happening with your store today.</span></h1>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#6B7280; background:#fff; border:1px solid #E9EDF2; border-radius:8px; padding:8px 14px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                {{ now()->subWeek()->format('d/m/Y') }} - {{ now()->format('d/m/Y') }}
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="sd-grid sd-grid-3" style="margin-bottom:24px;">
            <div class="sd-stat" style="display:flex; align-items:center; gap:14px;">
                <div class="sd-stat-icon" style="background:#E6F7E9; color:#198754;">{!! $icon('banknotes') !!}</div>
                <div style="flex:1; min-width:0;">
                    <p class="sd-stat-label">Weekly Earning</p>
                    <p class="sd-stat-value">{{ $d['currency'] }} {{ $money($d['weeklyEarning']) }}</p>
                </div>
                @if ($d['weeklyPct'] !== null)
                    <span style="background: {{ $d['weeklyPct'] >= 0 ? '#E6F7E9' : '#FFE6E6' }}; color: {{ $d['weeklyPct'] >= 0 ? '#198754' : '#DC3545' }}; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; white-space: nowrap;">{{ $d['weeklyPct'] >= 0 ? '&#8599;' : '&#8601;' }} {{ abs($d['weeklyPct']) }}%</span>
                @endif
            </div>
            <div class="sd-stat" style="display:flex; align-items:center; gap:14px; background:#FFF4E6; border-color:#FFF4E6;">
                <div class="sd-stat-icon" style="background:#fff; color:#E04F16;">{!! $icon('bag') !!}</div>
                <div style="flex:1; min-width:0;">
                    <p class="sd-stat-label" style="color:#9a6324;">No of Total Sales</p>
                    <p class="sd-stat-value" style="color:#E04F16;">{{ number_format($d['totalSales']) }}</p>
                </div>
            </div>
            <div class="sd-stat" style="display:flex; align-items:center; gap:14px; background:#092C4C; border-color:#092C4C;">
                <div class="sd-stat-icon" style="background:rgba(255,255,255,.15); color:#fff;">{!! $icon('truck') !!}</div>
                <div style="flex:1; min-width:0;">
                    <p class="sd-stat-label" style="color:rgba(255,255,255,.7);">No of Total Sales</p>
                    <p class="sd-stat-value" style="color:#fff;">{{ number_format($d['totalPurchases']) }}</p>
                </div>
            </div>
        </div>

        {{-- Best sellers + recent transactions --}}
        <div class="sd-grid sd-grid-2" style="margin-bottom:24px;">
            <div class="sd-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:34px; height:34px; border-radius:8px; background:#FCE7F3; color:#BE185D; display:flex; align-items:center; justify-content:center;">{!! $icon('trophy', 18) !!}</span>
                        <h4 class="sd-card-title">Best Seller</h4>
                    </div>
                    <a href="/tenant/best-seller" style="font-size:12px; color:#0D6EFD; text-decoration:underline; font-weight:500;">View All</a>
                </div>
                @forelse ($d['bestSellers'] as $i => $p)
                    <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #F3F4F6;">
                        <span class="sd-avatar" style="background:{{ $avatarColors[$i % 5] }};">
                            @if (! empty($p->image))
                                <img src="{{ $p->image }}" alt="{{ $p->product_name }}" />
                            @else
                                {{ $initial($p->product_name) }}
                            @endif
                        </span>
                        <div style="flex:1; min-width:0;">
                            <h6 style="margin:0; font-size:13px; font-weight:700; color:#1F2937; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $p->product_name }}</h6>
                            <p style="margin:2px 0 0; font-size:11px; color:#6B7280;">Sales {{ number_format((int) $p->total_sold) }}</p>
                        </div>
                        <span style="font-size:13px; font-weight:700; color:#1F2937;">{{ $d['currency'] }} {{ $money((float) $p->total_sold * (float) $p->avg_price) }}</span>
                    </div>
                @empty
                    <p style="color:#6B7280; font-size:12px; text-align:center; padding:20px 0;">No sales data yet</p>
                @endforelse
            </div>

            <div class="sd-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:34px; height:34px; border-radius:8px; background:#FFE9E0; color:#E04F16; display:flex; align-items:center; justify-content:center;">{!! $icon('clock', 18) !!}</span>
                        <h4 class="sd-card-title">Recent Transactions</h4>
                    </div>
                    <a href="/tenant/sales-page" style="font-size:12px; color:#0D6EFD; text-decoration:underline; font-weight:500;">View All</a>
                </div>
                <table class="sd-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order Details</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($d['recentTransactions'] as $idx => $t)
                            @php
                                $b = $badge($t->status);
                                $firstItem = $d['recentTransactionItems'][$t->receipt_number] ?? null;
                            @endphp
                            <tr>
                                <td style="color:#6B7280;">{{ $idx + 1 }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        @if ($firstItem && !empty($firstItem->image))
                                            <img src="{{ $firstItem->image }}" alt="" style="width:36px; height:36px; border-radius:6px; object-fit:cover; flex-shrink:0;" />
                                        @else
                                            <span style="width:36px; height:36px; border-radius:6px; background:#F3F4F6; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{!! $icon('bag', 16) !!}</span>
                                        @endif
                                        <div>
                                            <p style="margin:0; font-size:13px; font-weight:600; color:#1F2937;">{{ $firstItem ? $firstItem->product_name : ($t->customer_name ?: 'Walk-in') }}</p>
                                            <p style="margin:2px 0 0; font-size:11px; color:#E04F16;">#{{ $t->receipt_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p style="margin:0; font-size:12px; color:#6B7280;">{{ $t->payment_method ?: '&mdash;' }}</p>
                                        <p style="margin:2px 0 0; font-size:10px; color:#9CA3AF;">#{{ $t->receipt_number }}</p>
                                    </div>
                                </td>
                                <td><span style="background:{{ $b[0] }}; color:{{ $b[1] }}; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:600; white-space:nowrap;">{{ $badgeLabel($t->status) }}</span></td>
                                <td style="text-align:right; font-weight:700;">{{ $d['currency'] }} {{ $money((float) $t->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center; color:#6B7280; padding:20px 0;">No transactions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sales analytics + category donut --}}
        <div class="sd-grid sd-grid-2">
            <div class="sd-card">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:34px; height:34px; border-radius:8px; background:#FFE6E6; color:#DC3545; display:flex; align-items:center; justify-content:center;">{!! $icon('chart-bar', 18) !!}</span>
                        <div>
                            <h4 class="sd-card-title">Sales Analytics</h4>
                            <p style="margin:2px 0 0; font-size:11px; color:#6B7280;">Revenue vs returns for {{ now()->year }}</p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; font-size:12px; color:#6B7280;">
                        <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#0E9384;"></span>Revenue</span>
                        <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#E04F16;"></span>Returns</span>
                    </div>
                </div>
                <div style="position:relative; height:200px;">
                    <div style="position:absolute; left:0; right:0; top:50%; height:1px; background:#E9EDF2;"></div>
                    <div style="display:flex; gap:5px; height:100%; align-items:stretch;">
                        @foreach ($d['chartLabels'] as $i => $label)
                            @php
                                $revH = round(($d['revenueChart'][$i] / $d['maxAnalytics']) * 45);
                                $retH = round((abs($d['returnsChart'][$i]) / $d['maxAnalytics']) * 45);
                            @endphp
                            <div style="flex:1; position:relative;" title="{{ $label }} &mdash; Revenue {{ $d['currency'] }} {{ $money($d['revenueChart'][$i]) }}, Returns {{ $d['currency'] }} {{ $money(abs($d['returnsChart'][$i])) }}">
                                @if ($revH > 0)<div style="position:absolute; left:0; right:0; bottom:50%; height:{{ $revH }}%; background:#0E9384; border-radius:3px 3px 0 0;"></div>@endif
                                @if ($retH > 0)<div style="position:absolute; left:0; right:0; top:50%; height:{{ $retH }}%; background:#E04F16; border-radius:0 0 3px 3px;"></div>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    @foreach ($d['chartLabels'] as $l)
                        <span style="font-size:11px; color:#9CA3AF;">{{ $l }}</span>
                    @endforeach
                </div>
            </div>

            <div class="sd-card">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:34px; height:34px; border-radius:8px; background:#E4E6FF; color:#4B49AC; display:flex; align-items:center; justify-content:center;">{!! $icon('tag', 18) !!}</span>
                        <div>
                            <h4 class="sd-card-title">Sales by Category</h4>
                            <p style="margin:2px 0 0; font-size:11px; color:#6B7280;">Top selling categories</p>
                        </div>
                    </div>
                    <span style="font-size:12px; color:#6B7280;">This Year</span>
                </div>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="position:relative; width:130px; height:130px; border-radius:50%; background:{{ $donutGradient }}; flex-shrink:0;">
                        <div style="position:absolute; inset:28px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                            <span style="font-size:16px; font-weight:800; color:#1F2937;">{{ number_format($d['topCategories']->sum('qty')) }}</span>
                            <span style="font-size:10px; color:#6B7280;">Sales</span>
                        </div>
                    </div>
                    <div style="flex:1; min-width:0;">
                        @forelse ($d['topCategories'] as $i => $cat)
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                <span style="width:10px; height:10px; border-radius:50%; background:{{ $catColors[$i % 4] }}; flex-shrink:0;"></span>
                                <div style="flex:1; min-width:0;">
                                    <p style="margin:0; font-size:12px; font-weight:600; color:#1F2937; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $cat->name }}</p>
                                    <p style="margin:2px 0 0; font-size:11px; color:#6B7280;">{{ number_format((int) $cat->qty) }} Sales</p>
                                </div>
                            </div>
                        @empty
                            <p style="color:#6B7280; font-size:12px;">No category sales yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>