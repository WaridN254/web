<x-filament-panels::page>
    <style>
        .ps-serial-stats {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .ps-serial-card {
            border-radius: 10px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .ps-serial-card .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .ps-serial-card .value { font-size: 24px; font-weight: 800; }
        .ps-card-available { border-left: 4px solid #16a34a; }
        .ps-card-available .value { color: #16a34a; }
        .ps-card-sold { border-left: 4px solid #dc2626; }
        .ps-card-sold .value { color: #dc2626; }
        .ps-card-returned { border-left: 4px solid #f59e0b; }
        .ps-card-returned .value { color: #f59e0b; }
        .ps-card-reserved { border-left: 4px solid #2563eb; }
        .ps-card-reserved .value { color: #2563eb; }
        .ps-card-transferred { border-left: 4px solid #6b7280; }
        .ps-card-transferred .value { color: #6b7280; }
        .ps-card-damaged { border-left: 4px solid #e11d48; }
        .ps-card-damaged .value { color: #e11d48; }
        .ps-modal-overlay { position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px); }
        .ps-modal { background:#fff;border-radius:14px;width:92%;max-width:800px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden; }
        .ps-modal-head { display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0; }
        .ps-modal-head h3 { margin:0;font-size:16px;font-weight:700;color:#0f172a; }
        .ps-modal-close { background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;padding:2px 6px;border-radius:6px; }
        .ps-modal-close:hover { color:#475569; }
        .ps-modal-body { flex:1;overflow-y:auto;padding:20px; }
        .ps-modal-table { width:100%;border-collapse:collapse;font-size:13px; }
        .ps-modal-table th { text-align:left;padding:8px 10px;border-bottom:2px solid #e2e8f0;font-weight:600;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.04em; }
        .ps-modal-table td { padding:8px 10px;border-bottom:1px solid #f1f5f9; }
        .ps-modal-table tr:hover td { background:#f8fafc; }
        .ps-status-badge { display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600; }
        .ps-status-available { background:#dcfce7;color:#16a34a; }
        .ps-status-sold { background:#fee2e2;color:#dc2626; }
        .ps-status-returned { background:#fef3c7;color:#d97706; }
        .ps-status-reserved { background:#dbeafe;color:#2563eb; }
        .ps-status-transferred { background:#f3f4f6;color:#6b7280; }
        .ps-status-damaged { background:#ffe4e6;color:#e11d48; }
        .ps-modal-footer { padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end; }
        .ps-modal-btn { padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;background:#2563eb;color:#fff; }
        .ps-modal-btn:hover { background:#1d4ed8; }
    </style>

    @php
        $tenantId = auth()->user()?->tenant_id;
        $stats = \Illuminate\Support\Facades\DB::table('product_serials')
            ->where('tenant_id', $tenantId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');
    @endphp

    <div class="ps-serial-stats">
        <div class="ps-serial-card ps-card-available">
            <span class="label" style="color:#16a34a;">Available</span>
            <span class="value">{{ number_format($stats['available'] ?? 0) }}</span>
        </div>
        <div class="ps-serial-card ps-card-sold">
            <span class="label" style="color:#dc2626;">Sold</span>
            <span class="value">{{ number_format($stats['sold'] ?? 0) }}</span>
        </div>
        <div class="ps-serial-card ps-card-returned">
            <span class="label" style="color:#f59e0b;">Returned</span>
            <span class="value">{{ number_format($stats['returned'] ?? 0) }}</span>
        </div>
        <div class="ps-serial-card ps-card-reserved">
            <span class="label" style="color:#2563eb;">Reserved</span>
            <span class="value">{{ number_format($stats['reserved'] ?? 0) }}</span>
        </div>
        <div class="ps-serial-card ps-card-transferred">
            <span class="label" style="color:#6b7280;">Transferred</span>
            <span class="value">{{ number_format($stats['transferred'] ?? 0) }}</span>
        </div>
        <div class="ps-serial-card ps-card-damaged">
            <span class="label" style="color:#e11d48;">Damaged</span>
            <span class="value">{{ number_format($stats['damaged'] ?? 0) }}</span>
        </div>
    </div>

    {{ $this->table }}

    @if ($showSerialsModal)
        <div class="ps-modal-overlay" wire:click.self="$set('showSerialsModal', false)">
            <div class="ps-modal" wire:key="serials-modal">
                <div class="ps-modal-head">
                    <h3>{{ $modalProductName }} — Serial Numbers</h3>
                    <button type="button" class="ps-modal-close" wire:click="closeSerialsModal">&times;</button>
                </div>
                <div class="ps-modal-body">
                    @if (count($modalSerials) > 0)
                        <table class="ps-modal-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Serial Number</th>
                                    <th>Box Serial</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                    <th>Price</th>
                                    <th>Received</th>
                                    <th>Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modalSerials as $index => $serial)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="font-weight:600;">{{ $serial['serial_number'] }}</td>
                                        <td>{{ $serial['box_serial_number'] }}</td>
                                        <td>
                                            <span class="ps-status-badge ps-status-{{ $serial['status'] }}">
                                                {{ ucfirst($serial['status']) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($serial['cost_price'], 0) }}</td>
                                        <td>{{ number_format($serial['selling_price'], 0) }}</td>
                                        <td>{{ $serial['received_at'] }}</td>
                                        <td>{{ $serial['sold_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="text-align:center;padding:40px;color:#94a3b8;">
                            <p style="font-size:14px;">No serial numbers found for this product.</p>
                        </div>
                    @endif
                </div>
                <div class="ps-modal-footer">
                    <button type="button" class="ps-modal-btn" wire:click="closeSerialsModal">Done</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
