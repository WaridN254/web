<x-filament-panels::page>
    <style>
        .psp-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .psp-card {
            border-radius: 10px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .psp-card .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .psp-card .value { font-size: 24px; font-weight: 800; }
        .psp-products { border-left: 4px solid #6366f1; }
        .psp-products .value { color: #6366f1; }
        .psp-total { border-left: 4px solid #0ea5e9; }
        .psp-total .value { color: #0ea5e9; }
        .psp-available { border-left: 4px solid #16a34a; }
        .psp-available .value { color: #16a34a; }
        .psp-sold { border-left: 4px solid #dc2626; }
        .psp-sold .value { color: #dc2626; }
    </style>

    @php
        $tenantId = auth()->user()?->tenant_id;
        $productCount = \Illuminate\Support\Facades\DB::table('products')
            ->where('tenant_id', $tenantId)
            ->where('track_serial_numbers', true)
            ->count();
        $serialStats = \Illuminate\Support\Facades\DB::table('product_serials')
            ->where('tenant_id', $tenantId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalSerials = $serialStats->sum();
    @endphp

    <div class="psp-stats">
        <div class="psp-card psp-products">
            <span class="label" style="color:#6366f1;">Products with Serials</span>
            <span class="value">{{ number_format($productCount) }}</span>
        </div>
        <div class="psp-card psp-total">
            <span class="label" style="color:#0ea5e9;">Total Serials</span>
            <span class="value">{{ number_format($totalSerials) }}</span>
        </div>
        <div class="psp-card psp-available">
            <span class="label" style="color:#16a34a;">Available</span>
            <span class="value">{{ number_format($serialStats['available'] ?? 0) }}</span>
        </div>
        <div class="psp-card psp-sold">
            <span class="label" style="color:#dc2626;">Sold</span>
            <span class="value">{{ number_format($serialStats['sold'] ?? 0) }}</span>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
