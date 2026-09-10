<x-filament-widgets::widget>
    @php
        $money = fn ($amount) => number_format((float) $amount, 0, '.', ',');
        $sign = fn ($v) => ($v !== null && $v >= 0 ? '+' : '') . number_format(abs((float) $v), 1);

        $I = [
            'banknotes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
            'arrow-path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>',
            'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
            'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
            'trending-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/>',
            'arrow-uturn-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>',
        ];
        $ic = fn ($name) => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $I[$name] . '</svg>';

        $mainStats = [
            ['label' => 'Gross Revenue', 'value' => $grossRevenue, 'delta' => $deltaGrossRevenue, 'icon' => $ic('banknotes'), 'bg' => '#0D6EFD'],
            ['label' => 'Net Revenue', 'value' => $netRevenue, 'delta' => $deltaNetRevenue, 'icon' => $ic('trending-up'), 'bg' => '#0E9384'],
            ['label' => 'Total Refunds', 'value' => $totalRefunds, 'delta' => $deltaRefunds, 'icon' => $ic('arrow-path'), 'bg' => '#6C757D'],
            ['label' => 'Total Purchase Return', 'value' => $totalPurchaseReturn, 'delta' => $deltaPurchaseReturn, 'icon' => $ic('tag'), 'bg' => '#0DCAF0'],
        ];

        $secStats = [
            ['label' => 'Profit', 'value' => $profit, 'delta' => $deltaProfit, 'icon' => $ic('trending-up'), 'tint' => '#E0F2EF', 'color' => '#0E9384', 'link' => '#'],
            ['label' => 'Invoice Due', 'value' => $invoiceDue, 'delta' => $deltaInvoiceDue, 'icon' => $ic('clock'), 'tint' => '#E0F5F2', 'color' => '#0E9384', 'link' => '/tenant/invoice-page'],
            ['label' => 'Total Expenses', 'value' => $actualExpenses, 'delta' => $deltaExpenses, 'icon' => $ic('wallet'), 'tint' => '#FFE9E0', 'color' => '#E04F16', 'link' => '#'],
            ['label' => 'Total Payment Returns', 'value' => $paymentReturns, 'delta' => $deltaPaymentReturns, 'icon' => $ic('arrow-uturn-left'), 'tint' => '#E4E6FF', 'color' => '#4B49AC', 'link' => '/tenant/sales-return-page'],
        ];
    @endphp

    {{-- Row 1: Solid colored cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @foreach ($mainStats as $stat)
            <div style="background: {{ $stat['bg'] }}; border-radius: 10px; padding: 18px; color: #fff; display: flex; align-items: center; gap: 14px;">
                <span style="width: 48px; height: 48px; border-radius: 50%; background: #fff; color: {{ $stat['bg'] }}; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">{!! $stat['icon'] !!}</span>
                <div style="flex: 1; min-width: 0;">
                    <p style="margin: 0; font-size: 13px; opacity: .9;">{{ $stat['label'] }}</p>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                        <span style="font-size: 19px; font-weight: 700; letter-spacing: -0.3px;">{{ $currency }} {{ $money($stat['value']) }}</span>
                        @if ($stat['delta'] !== null)
                            <span style="background: rgba(255,255,255,.25); border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600;">
                                {{ $stat['delta'] >= 0 ? '&#9650;' : '&#9660;' }} {!! $sign($stat['delta']) !!}%
                            </span>
                        @else
                            <span style="background: rgba(255,255,255,.18); border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600;">&mdash;</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Row 2: White revenue cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @foreach ($secStats as $stat)
            <div style="background: #fff; border: 1px solid #E9EDF2; border-radius: 10px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #E9EDF2; padding-bottom: 14px; margin-bottom: 12px;">
                    <div>
                        <h4 style="margin: 0; font-size: 19px; font-weight: 700; color: #1F2937;">{{ $currency }} {{ $money($stat['value']) }}</h4>
                        <p style="margin: 6px 0 0; font-size: 13px; color: #6B7280;">{{ $stat['label'] }}</p>
                    </div>
                    <span style="width: 42px; height: 42px; border-radius: 8px; background: {{ $stat['tint'] }}; color: {{ $stat['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">{!! $stat['icon'] !!}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="margin: 0; font-size: 13px; color: #6B7280;">
                        @if ($stat['delta'] !== null)
                            <span style="font-weight: 700; color: {{ $stat['delta'] >= 0 ? '#198754' : '#DC3545' }};">{!! $sign($stat['delta']) !!}%</span>
                        @else
                            <span style="font-weight: 700; color: #6B7280;">&mdash;</span>
                        @endif
                        &nbsp;vs Last Month
                    </p>
                    <a href="{{ $stat['link'] }}" style="text-decoration: underline; font-size: 13px; font-weight: 500; color: #0D6EFD;">View All</a>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>