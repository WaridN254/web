<x-filament-widgets::widget>
    @php
        $money = fn ($amount) => number_format((float) $amount, 0, '.', ',');
        $money2 = fn ($amount) => number_format((float) $amount, 2, '.', ',');
        $P = [
            'cart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>',
            'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
            'cube' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>',
            'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
            'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
            'archive' => '<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m10 6h2.25m-2.25 0a2.25 2.25 0 1 1-4.5 0m4.5 0H9m10.25-6h-15m15 0a2.25 2.25 0 0 0 0-4.5H5.25a2.25 2.25 0 0 0 0 4.5h15Z"/>',
            'store' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>',
            'pin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>',
        ];
        $icon = function ($name, $size = 18) use ($P) {
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $P[$name] . '</svg>';
        };
        $badge = function ($s) {
            $map = [
                'completed' => ['#E6F7E9', '#198754'], 'paid' => ['#E6F7E9', '#198754'],
                'sent' => ['#E6F7E9', '#198754'], 'approved' => ['#E6F7E9', '#198754'],
                'accepted' => ['#E6F7E9', '#198754'],
                'processing' => ['#F0E7FB', '#6F42C1'], 'resumed' => ['#F0E7FB', '#6F42C1'],
                'pending' => ['#E0F7FA', '#0E7490'], 'onhold' => ['#E0F7FA', '#0E7490'],
                'cancelled' => ['#FFE6E6', '#DC3545'], 'refunded' => ['#FFE6E6', '#DC3545'],
                'unpaid' => ['#FFE6E6', '#DC3545'],
                'draft' => ['#FCE7F3', '#BE185D'],
                'ordered' => ['#FFE9E0', '#E04F16'], 'overdue' => ['#FFE9E0', '#E04F16'],
            ];
            return $map[strtolower((string) $s)] ?? ['#EEF1F5', '#6C757D'];
        };
        $badgeLabel = fn ($s) => ucfirst(strtolower((string) $s));
        $avatarColors = ['#FE9F43', '#0E9384', '#4B49AC', '#E04F16', '#6F42C1'];
        $avatarBg = fn ($i, $name) => $avatarColors[$i % count($avatarColors)];
        $initial = fn ($name) => strtoupper(mb_substr((string) $name, 0, 1));
        $maxHourly = max(1, max($salesHourly), max($purchaseHourly));
        $catColors = ['#FE9F43', '#E04F16', '#092C4C', '#0E9384'];
        $catSum = max(1, (float) $topCategories->sum('qty'));
        $donutGradient = '';
        $acc = 0;
        foreach ($topCategories as $i => $cat) {
            $pct = ((float) $cat->qty / $catSum) * 360;
            $donutGradient .= $catColors[$i % 4] . ' ' . round($acc, 1) . 'deg ' . round($acc + $pct, 1) . 'deg, ';
            $acc += $pct;
        }
        $donutGradient = $donutGradient ? rtrim($donutGradient, ', ') : '#E9EDF2 0 360deg';
        $maxStat = max(1, (float) max(array_merge($chartRevenue, array_map('abs', $chartExpense))));
        $custTotal = $firstTimeCount + $returnCount;
        $firstPct = $custTotal ? round(($firstTimeCount / $custTotal) * 360, 1) : 0;
        $heatDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $heatRows = ['2-5 AM', '6-9 AM', '10 AM-1 PM', '2-5 PM', '6-9 PM', '10 PM-1 AM'];
        $maxHeat = 1;
        foreach ($heatmap as $row) { $maxHeat = max($maxHeat, max($row)); }
    @endphp

    {{-- Row A: Sales & Purchase + Overall Information --}}
    <div style="display: grid; grid-template-columns: 7fr 5fr; gap: 20px; margin-bottom: 24px;">
        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="width: 42px; height: 42px; border-radius: 8px; background: #EAF2FE; color: #0D6EFD; display: flex; align-items: center; justify-content: center;">{!! $icon('cart', 20) !!}</span>
                    <div style="display: flex; gap: 12px;">
                        <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 8px 12px; min-width: 90px;">
                            <p style="margin: 0; font-size: 11px; color: #6B7280;"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#FFE3CB; margin-right:4px;"></span>Total Purchase</p>
                            <h4 style="margin: 4px 0 0; font-size: 17px; font-weight: 700; color: #1F2937;">{{ $purchaseCountToday }}</h4>
                        </div>
                        <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 8px 12px; min-width: 90px;">
                            <p style="margin: 0; font-size: 11px; color: #6B7280;"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#FE9F43; margin-right:4px;"></span>Total Sales</p>
                            <h4 style="margin: 4px 0 0; font-size: 17px; font-weight: 700; color: #1F2937;">{{ $saleCountToday }}</h4>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    @foreach (['1D', '1W', '1M', '3M', '6M', '1Y'] as $period)
                        <button style="background: {{ $period === '1Y' ? '#FE9F43' : 'transparent' }}; color: {{ $period === '1Y' ? '#fff' : '#6B7280' }}; border: 1px solid {{ $period === '1Y' ? '#FE9F43' : '#E9EDF2' }}; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600;">{{ $period }}</button>
                    @endforeach
                </div>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 3px; height: 210px;">
                @for ($h = 0; $h < 24; $h++)
                    @php
                        $s = $salesHourly[$h]; $p = $purchaseHourly[$h];
                        $sh = round(($s / $maxHourly) * 100); $ph = round(($p / $maxHourly) * 100);
                    @endphp
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end; gap: 2px; height: 100%;" title="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 &mdash; Sales {{ $s }}, Purchase {{ $p }}">
                        @if ($ph > 0)<div style="height: {{ $ph }}%; background: #FFE3CB; border-radius: 2px;"></div>@endif
                        @if ($sh > 0)<div style="height: {{ $sh }}%; background: #FE9F43; border-radius: 2px;"></div>@endif
                    </div>
                @endfor
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                @foreach (['2 AM', '6 AM', '10 AM', '2 PM', '6 PM', '10 PM'] as $t)
                    <span style="font-size: 11px; color: #9CA3AF;">{{ $t }}</span>
                @endforeach
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px; display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px;">
                <span style="width: 42px; height: 42px; border-radius: 8px; background: #EAF2FE; color: #0D6EFD; display: flex; align-items: center; justify-content: center;">{!! $icon('users', 20) !!}</span>
                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Overall Information</h4>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 12px; text-align: center;">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #E0F7FA; color: #0DCAF0; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">{!! $icon('user', 16) !!}</span>
                    <p style="margin: 0; font-size: 12px; color: #6B7280;">Suppliers</p>
                    <h4 style="margin: 4px 0 0; font-size: 18px; font-weight: 700; color: #1F2937;">{{ $supplierCount }}</h4>
                </div>
                <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 12px; text-align: center;">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #FFE9E0; color: #E04F16; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">{!! $icon('users', 16) !!}</span>
                    <p style="margin: 0; font-size: 12px; color: #6B7280;">Customer</p>
                    <h4 style="margin: 4px 0 0; font-size: 18px; font-weight: 700; color: #1F2937;">{{ $customerCount }}</h4>
                </div>
                <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 12px; text-align: center;">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #E0F2EF; color: #0E9384; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">{!! $icon('cart', 16) !!}</span>
                    <p style="margin: 0; font-size: 12px; color: #6B7280;">Orders</p>
                    <h4 style="margin: 4px 0 0; font-size: 18px; font-weight: 700; color: #1F2937;">{{ $orderCount }}</h4>
                </div>
            </div>
            <div style="margin-top: auto; padding-top: 18px; border-top: 1px solid #E9EDF2;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h5 style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937;">Customers Overview</h5>
                    <span style="font-size: 12px; color: #6B7280; display: inline-flex; align-items: center; gap: 4px;">{!! $icon('calendar', 12) !!} Today</span>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="position: relative; width: 110px; height: 110px; border-radius: 50%; background: conic-gradient(#E04F16 0deg {{ $firstPct }}deg, #0E9384 {{ $firstPct }}deg 360deg); flex-shrink: 0;">
                        <div style="position: absolute; inset: 16px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                            <span style="font-size: 15px; font-weight: 700; color: #1F2937;">{{ $custTotal }}</span>
                            <span style="font-size: 10px; color: #6B7280;">Total</span>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div>
                                <p style="margin: 0; font-size: 16px; font-weight: 700; color: #E04F16;">{{ $firstTimeCount }}</p>
                                <p style="margin: 2px 0 0; font-size: 12px; color: #6B7280;">First Time</p>
                            </div>
                            <span style="background: #E6F7E9; color: #198754; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600;">&#8598; {{ $custTotal ? round(($firstTimeCount / $custTotal) * 100) : 0 }}%</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="margin: 0; font-size: 16px; font-weight: 700; color: #0E9384;">{{ $returnCount }}</p>
                                <p style="margin: 2px 0 0; font-size: 12px; color: #6B7280;">Return</p>
                            </div>
                            <span style="background: #E6F7E9; color: #198754; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600;">&#8598; {{ $custTotal ? round(($returnCount / $custTotal) * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row B: Top Selling / Low Stock / Recent Sales --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FCE7F3; color: #BE185D; display: flex; align-items: center; justify-content: center;">{!! $icon('cube', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Top Selling Products</h4>
                </div>
                <span style="font-size: 12px; color: #6B7280;">Today &#9660;</span>
            </div>
            @forelse ($topSelling->take(5) as $i => $product)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #F3F4F6;">
                    <span style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: {{ $avatarBg($i, $product->name) }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0;">
                        @if (! empty($product->image))
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                        @else
                            {{ $initial($product->name) }}
                        @endif
                    </span>
                    <div style="flex: 1; min-width: 0;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $product->name }}</h6>
                        <div style="display: flex; gap: 12px; margin-top: 2px; font-size: 11px; color: #6B7280;">
                            <span>{{ $currency }} {{ $money($product->price) }}</span>
                            <span>{{ (int) $product->total_sold }}+ Sales</span>
                        </div>
                    </div>
                    @if ($product->delta !== null)
                        <span style="background: {{ $product->delta >= 0 ? '#E6F7E9' : '#FFE6E6' }}; color: {{ $product->delta >= 0 ? '#198754' : '#DC3545' }}; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; white-space: nowrap;">{{ $product->delta >= 0 ? '&#8599;' : '&#8601;' }} {{ abs($product->delta) }}%</span>
                    @else
                        <span style="background: #EEF1F5; color: #6C757D; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600;">&mdash;</span>
                    @endif
                </div>
            @empty
                <p style="color: #6B7280; font-size: 12px; text-align: center; padding: 20px 0;">No sales data yet</p>
            @endforelse
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FFE9E0; color: #E04F16; display: flex; align-items: center; justify-content: center;">{!! $icon('warning', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Low Stock Products</h4>
                </div>
                <a href="/tenant/stock-dashboard" style="font-size: 12px; color: #0D6EFD; text-decoration: underline; font-weight: 500;">View All</a>
            </div>
            @forelse ($lowStock->take(5) as $i => $product)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #F3F4F6;">
                    <span style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: {{ $avatarBg($i, $product->name) }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0;">
                        @if (! empty($product->image))
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                        @else
                            {{ $initial($product->name) }}
                        @endif
                    </span>
                    <div style="flex: 1; min-width: 0;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $product->name }}</h6>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #6B7280;">ID : #{{ substr((string) $product->id, 0, 6) }}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 11px; color: #6B7280;">Instock</p>
                        <p style="margin: 2px 0 0; font-size: 14px; font-weight: 700; color: #E04F16;">{{ (int) $product->current_stock }}</p>
                    </div>
                </div>
            @empty
                <p style="color: #6B7280; font-size: 12px; text-align: center; padding: 20px 0;">No low stock items</p>
            @endforelse
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FCE7F3; color: #BE185D; display: flex; align-items: center; justify-content: center;">{!! $icon('clock', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Recent Sales</h4>
                </div>
                <span style="font-size: 12px; color: #6B7280;">Weekly &#9660;</span>
            </div>
            @forelse ($recentSales->take(5) as $i => $sale)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #F3F4F6;">
                    <span style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: {{ $avatarBg($i, $sale->product_name) }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0;">
                        @if (! empty($sale->image))
                            <img src="{{ $sale->image }}" alt="{{ $sale->product_name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                        @else
                            {{ $initial($sale->product_name) }}
                        @endif
                    </span>
                    <div style="flex: 1; min-width: 0;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $sale->product_name }}</h6>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #6B7280;">{{ $sale->category_name ?? 'Uncategorized' }}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money2($sale->unit_price) }}</p>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #6B7280;">{{ \Carbon\Carbon::parse($sale->transaction_date)->format('d M Y') }}</p>
                    </div>
                    @php $b = $badge($sale->status); @endphp
                    <span style="background: {{ $b[0] }}; color: {{ $b[1] }}; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; white-space: nowrap;">{{ $badgeLabel($sale->status) }}</span>
                </div>
            @empty
                <p style="color: #6B7280; font-size: 12px; text-align: center; padding: 20px 0;">No recent sales</p>
            @endforelse
        </div>
    </div>

    {{-- Row C: Sales Statistics / Top Categories / Order Statistics --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FFE6E6; color: #DC3545; display: flex; align-items: center; justify-content: center;">{!! $icon('chart', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Sales Statistics</h4>
                </div>
                <select style="font-size: 12px; border: 1px solid #E9EDF2; border-radius: 6px; padding: 5px 8px; background: #fff; color: #6B7280; cursor: pointer;">
                    <option>{{ now()->year }}</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 10px 12px; flex: 1;">
                    <p style="margin: 0; font-size: 11px; color: #0E9384;"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#0E9384; margin-right:4px;"></span>Revenue</p>
                    <h4 style="margin: 4px 0 0; font-size: 16px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money($totalRevenue) }}</h4>
                </div>
                <div style="border: 1px solid #E9EDF2; border-radius: 8px; padding: 10px 12px; flex: 1;">
                    <p style="margin: 0; font-size: 11px; color: #E04F16;"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#E04F16; margin-right:4px;"></span>Expense</p>
                    <h4 style="margin: 4px 0 0; font-size: 16px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money($totalExpense) }}</h4>
                </div>
            </div>
            <div style="position: relative; height: 190px;">
                <div style="position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: #E9EDF2;"></div>
                <div style="display: flex; gap: 5px; height: 100%; align-items: stretch;">
                    @foreach ($chartLabels as $i => $label)
                        @php
                            $rev = $chartRevenue[$i]; $exp = abs($chartExpense[$i]);
                            $revH = round(($rev / $maxStat) * 47); $expH = round(($exp / $maxStat) * 47);
                        @endphp
                        <div style="flex: 1; position: relative;" title="{{ $label }} &mdash; Rev {{ $currency }} {{ $money($rev) }}, Exp {{ $currency }} {{ $money($exp) }}">
                            @if ($revH > 0)<div style="position: absolute; left: 0; right: 0; bottom: 50%; height: {{ $revH }}%; background: #0E9384; border-radius: 3px 3px 0 0;"></div>@endif
                            @if ($expH > 0)<div style="position: absolute; left: 0; right: 0; top: 50%; height: {{ $expH }}%; background: #E04F16; border-radius: 0 0 3px 3px;"></div>@endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                @foreach (['Jan', 'Apr', 'Jul', 'Oct', 'Dec'] as $l)
                    <span style="font-size: 11px; color: #9CA3AF;">{{ $l }}</span>
                @endforeach
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FFE9E0; color: #E04F16; display: flex; align-items: center; justify-content: center;">{!! $icon('tag', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Top Categories</h4>
                </div>
                <span style="font-size: 12px; color: #6B7280;">Weekly &#9660;</span>
            </div>
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 18px;">
                <div style="position: relative; width: 120px; height: 120px; border-radius: 50%; background: {{ $donutGradient }}; flex-shrink: 0;">
                    <div style="position: absolute; inset: 26px; border-radius: 50%; background: #fff;"></div>
                </div>
                <div style="flex: 1; min-width: 0;">
                    @foreach ($topCategories as $i => $cat)
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $catColors[$i % 4] }}; flex-shrink: 0;"></span>
                            <div style="flex: 1; min-width: 0;">
                                <p style="margin: 0; font-size: 12px; font-weight: 600; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $cat->name }}</p>
                                <p style="margin: 2px 0 0; font-size: 11px; color: #6B7280;">{{ (int) $cat->qty }} Sales</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div style="border-top: 1px solid #E9EDF2; padding-top: 14px;">
                <p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; color: #6B7280;">Category Statistics</p>
                <div style="display: flex; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #4B49AC;"></span>
                        <span style="font-size: 12px; color: #6B7280;">Total Categories <b style="color:#1F2937;">{{ $totalCats }}</b></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #FE9F43;"></span>
                        <span style="font-size: 12px; color: #6B7280;">Total Products <b style="color:#1F2937;">{{ $totalProducts }}</b></span>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #E4E6FF; color: #4B49AC; display: flex; align-items: center; justify-content: center;">{!! $icon('archive', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Order Statistics</h4>
                </div>
                <span style="font-size: 12px; color: #6B7280;">Weekly &#9660;</span>
            </div>
            <div style="display: grid; grid-template-columns: 64px repeat(7, 1fr); gap: 4px; align-items: center;">
                <div></div>
                @foreach ($heatDays as $d)
                    <div style="text-align: center; font-size: 10px; color: #6B7280; font-weight: 600;">{{ $d }}</div>
                @endforeach
                @foreach ($heatRows as $ri => $rlabel)
                    <div style="font-size: 10px; color: #6B7280;">{{ $rlabel }}</div>
                    @foreach ($heatmap[$ri] as $val)
                        @php
                            $a = $val > 0 ? 0.15 + 0.85 * ($val / $maxHeat) : 0.06;
                            $tc = $val / $maxHeat > 0.6 ? '#fff' : '#8A5A14';
                        @endphp
                        <div style="height: 26px; border-radius: 5px; background: rgba(254, 159, 67, {{ $a }}); color: {{ $tc }}; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 600;">{{ $val > 0 ? $val : '' }}</div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    {{-- Row D: Recent Transactions + Top Customers --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FFE9E0; color: #E04F16; display: flex; align-items: center; justify-content: center;">{!! $icon('store', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Recent Transactions</h4>
                </div>
                <a href="/tenant/sales-page" style="font-size: 12px; color: #0D6EFD; text-decoration: underline; font-weight: 500;">View All</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); border-bottom: 1px solid #E9EDF2; margin-bottom: 12px;">
                @foreach (['sale' => 'Sale', 'purchase' => 'Purchase', 'quotation' => 'Quotation', 'expenses' => 'Expenses', 'invoices' => 'Invoices'] as $key => $label)
                    <button id="trans-tab-{{ $key }}" onclick="switchTransTab('{{ $key }}')" style="background:none; border:none; border-bottom: 2px solid {{ $key === 'sale' ? '#FE9F43' : 'transparent' }}; padding: 8px 4px; font-size: 12px; font-weight: 600; color: {{ $key === 'sale' ? '#1F2937' : '#6B7280' }}; cursor: pointer; white-space: nowrap;">{{ $label }}</button>
                @endforeach
            </div>

            @foreach (['sale' => $txSale, 'purchase' => $txPurchase, 'quotation' => $txQuotation, 'expenses' => $txExpenses, 'invoices' => $txInvoice] as $key => $rows)
                <div id="trans-{{ $key }}" class="trans-pane" style="{{ $key === 'sale' ? '' : 'display: none;' }}">
                    @forelse ($rows as $row)
                        @php
                            if ($key === 'sale') {
                                $date = \Carbon\Carbon::parse($row->transaction_date)->format('d M Y');
                                $name = $row->customer_name; $status = $row->status; $total = $row->total_amount; $ref = $row->receipt_number;
                            } elseif ($key === 'purchase') {
                                $date = \Carbon\Carbon::parse($row->purchase_date)->format('d M Y');
                                $name = $row->supplier ?? 'Supplier'; $status = $row->status; $total = $row->grand_total; $ref = $row->purchase_number;
                            } elseif ($key === 'quotation') {
                                $date = \Carbon\Carbon::parse($row->created_at)->format('d M Y');
                                $name = $row->customer_name; $status = $row->status; $total = $row->total_amount; $ref = $row->quote_number;
                            } elseif ($key === 'expenses') {
                                $date = \Carbon\Carbon::parse($row->movement_date)->format('d M Y');
                                $name = $row->reason ?: $row->category; $status = 'approved'; $total = $row->amount; $ref = '';
                            } else {
                                $date = \Carbon\Carbon::parse($row->transaction_date)->format('d M Y');
                                $name = $row->customer_name; $status = $row->settlement_status; $total = $row->balance_due; $ref = $row->receipt_number;
                            }
                            $b = $badge($status);
                        @endphp
                        <div style="display: grid; grid-template-columns: 78px 1fr auto auto; gap: 10px; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6;">
                            <span style="font-size: 11px; color: #6B7280;">{{ $date }}</span>
                            <div style="min-width: 0;">
                                <p style="margin: 0; font-size: 12px; font-weight: 700; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $name }}</p>
                                @if ($ref)<p style="margin: 2px 0 0; font-size: 11px; color: #E04F16;">#{{ $ref }}</p>@endif
                            </div>
                            <span style="background: {{ $b[0] }}; color: {{ $b[1] }}; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; white-space: nowrap;">{{ $badgeLabel($status) }}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money2($total) }}</span>
                        </div>
                    @empty
                        <p style="color: #6B7280; font-size: 12px; text-align: center; padding: 16px 0;">No {{ $key }} records</p>
                    @endforelse
                </div>
            @endforeach
        </div>

        <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 8px; background: #FFE9E0; color: #E04F16; display: flex; align-items: center; justify-content: center;">{!! $icon('users', 18) !!}</span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2937;">Top Customers</h4>
                </div>
                <a href="#" style="font-size: 12px; color: #0D6EFD; text-decoration: underline; font-weight: 500;">View All</a>
            </div>
            @forelse ($topCustomers->take(5) as $i => $cust)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #F3F4F6;">
                    <span style="width: 40px; height: 40px; border-radius: 50%; background: {{ $avatarBg($i, $cust->full_name) }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0;">{{ $initial($cust->full_name) }}</span>
                    <div style="flex: 1; min-width: 0;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 700; color: #1F2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $cust->full_name }}</h6>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #6B7280;">{!! $icon('pin', 12) !!} {{ $cust->address ?: ($cust->phone ?: 'N/A') }}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 11px; color: #6B7280;">{{ (int) $cust->total_orders }} Orders</p>
                        <p style="margin: 2px 0 0; font-size: 13px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money($cust->total_spent) }}</p>
                    </div>
                </div>
            @empty
                <p style="color: #6B7280; font-size: 12px; text-align: center; padding: 20px 0;">No customers yet</p>
            @endforelse
        </div>
    </div>

    @script
        <script>
            window.switchTransTab = function (tab) {
                document.querySelectorAll('.trans-pane').forEach(function (p) { p.style.display = 'none'; });
                document.querySelectorAll('[id^="trans-tab-"]').forEach(function (b) {
                    b.style.borderBottom = '2px solid transparent';
                    b.style.color = '#6B7280';
                });
                var pane = document.getElementById('trans-' + tab);
                var btn = document.getElementById('trans-tab-' + tab);
                if (pane) { pane.style.display = 'block'; }
                if (btn) { btn.style.borderBottom = '2px solid #FE9F43'; btn.style.color = '#1F2937'; }
            };
        </script>
    @endscript
</x-filament-widgets::widget>