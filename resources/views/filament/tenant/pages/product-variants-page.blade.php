<x-filament-panels::page>
    <style>
        .pv-overlay { position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); }
        .pv-modal { background:#fff; border-radius:14px; width:95%; max-width:900px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden; }
        .pv-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
        .pv-head h3 { margin:0; font-size:16px; font-weight:700; color:#0f172a; }
        .pv-close { background:none; border:none; cursor:pointer; color:#94a3b8; font-size:22px; padding:2px 6px; border-radius:6px; }
        .pv-close:hover { color:#475569; background:#f1f5f9; }
        .pv-body { flex:1; overflow-y:auto; padding:0; }
        .pv-footer { padding:14px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; }
        .pv-field { margin-bottom:14px; }
        .pv-field label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px; }
        .pv-field input, .pv-field select { width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; }
        .pv-btn { padding:8px 18px; border-radius:8px; border:none; font-size:13px; font-weight:600; cursor:pointer; }
        .pv-btn-primary { background:#2563eb; color:#fff; }
        .pv-btn-primary:hover { background:#1d4ed8; }
        .pv-btn-secondary { background:#f1f5f9; color:#475569; }
        .pv-btn-secondary:hover { background:#e2e8f0; }
        .pv-btn-danger { background:#dc2626; color:#fff; }
        .pv-btn-danger:hover { background:#b91c1c; }
        .pv-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .pv-stat-cards { display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; padding:16px 20px; border-bottom:1px solid #f1f5f9; background:#fafbfc; }
        .pv-stat { text-align:center; padding:12px 8px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; }
        .pv-stat .pv-stat-value { font-size:22px; font-weight:800; color:#0f172a; }
        .pv-stat .pv-stat-label { font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.04em; margin-top:2px; }
        .pv-table { width:100%; border-collapse:collapse; font-size:13px; }
        .pv-table th { text-align:left; padding:10px 16px; background:#f8fafc; border-bottom:2px solid #e2e8f0; font-weight:600; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.04em; position:sticky; top:0; z-index:1; }
        .pv-table td { padding:10px 16px; border-bottom:1px solid #f1f5f9; }
        .pv-table tr:hover td { background:#f8fafc; }
        .pv-badge { display:inline-block; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:600; }
        .pv-badge-green { background:#dcfce7; color:#166534; }
        .pv-badge-red { background:#fee2e2; color:#991b1b; }
        .pv-badge-blue { background:#dbeafe; color:#1e40af; }
        .pv-badge-gray { background:#f1f5f9; color:#475569; }
        .pv-badge-yellow { background:#fef9c3; color:#854d0e; }
        .pv-icon-btn { background:none; border:none; cursor:pointer; padding:4px; border-radius:4px; }
        .pv-icon-btn:hover { background:#f1f5f9; }
        .pv-edit-overlay { position:fixed; inset:0; z-index:10000; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); }
        .pv-edit-modal { background:#fff; border-radius:14px; width:92%; max-width:580px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden; }
    </style>

    {{ $this->table }}

    @if ($showVariantsModal)
        <div class="pv-overlay" wire:click.self="closeVariantsModal">
            <div class="pv-modal">
                <div class="pv-head">
                    <h3>{{ $modalProductName }} — Variants</h3>
                    <button type="button" class="pv-close" wire:click="closeVariantsModal">&times;</button>
                </div>
                <div class="pv-body">
                    @php
                        $totalVariants = count($modalVariants);
                        $totalStock = collect($modalVariants)->sum('stock');
                        $inheritedCount = collect($modalVariants)->where('selling_price_mode', 'inherited')->count();
                        $customCount = collect($modalVariants)->where('selling_price_mode', 'custom')->count();
                    @endphp

                    <div class="pv-stat-cards">
                        <div class="pv-stat">
                            <div class="pv-stat-value">{{ $totalVariants }}</div>
                            <div class="pv-stat-label">Total Variants</div>
                        </div>
                        <div class="pv-stat">
                            <div class="pv-stat-value" style="color:#16a34a;">{{ number_format($totalStock) }}</div>
                            <div class="pv-stat-label">Total Stock</div>
                        </div>
                        <div class="pv-stat">
                            <div class="pv-stat-value" style="color:#2563eb;">{{ $inheritedCount }}</div>
                            <div class="pv-stat-label">Inherited Price</div>
                        </div>
                        <div class="pv-stat">
                            <div class="pv-stat-value" style="color:#f59e0b;">{{ $customCount }}</div>
                            <div class="pv-stat-label">Custom Price</div>
                        </div>
                    </div>

                    @if ($totalVariants > 0)
                        <table class="pv-table">
                            <thead>
                                <tr>
                                    <th>Variant</th>
                                    <th>SKU</th>
                                    <th>Barcode</th>
                                    <th>Selling Price</th>
                                    <th>Price Mode</th>
                                    <th>Stock</th>
                                    <th>Serialized</th>
                                    <th style="width:80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modalVariants as $variant)
                                    <tr>
                                        <td style="font-weight:600;color:#0f172a;">{{ $variant['name'] }}</td>
                                        <td style="font-family:monospace;color:#475569;">{{ $variant['sku'] }}</td>
                                        <td style="color:#94a3b8;">{{ $variant['barcode'] }}</td>
                                        <td style="font-weight:600;">{{ auth()->user()?->tenant?->business?->currency ?? 'UGX' }} {{ number_format($variant['selling_price']) }}</td>
                                        <td>
                                            @if ($variant['selling_price_mode'] === 'inherited')
                                                <span class="pv-badge pv-badge-gray">Inherited</span>
                                            @else
                                                <span class="pv-badge pv-badge-blue">Custom</span>
                                            @endif
                                        </td>
                                        <td style="font-weight:600; color:{{ $variant['stock'] <= 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($variant['stock']) }}</td>
                                        <td>
                                            @if ($variant['track_serial_numbers'])
                                                <span class="pv-badge pv-badge-green">Yes</span>
                                            @else
                                                <span class="pv-badge pv-badge-gray">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:4px;">
                                                <button type="button" class="pv-icon-btn" wire:click="openEditModal('{{ $variant['id'] }}')" title="Edit">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                </button>
                                                <button type="button" class="pv-icon-btn" wire:click="deactivateVariant('{{ $variant['id'] }}')" title="Deactivate"
                                                    onclick="return confirm('Deactivate this variant?')">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding:40px;text-align:center;color:#94a3b8;">No variants found for this product.</div>
                    @endif
                </div>
                <div class="pv-footer">
                    <button type="button" class="pv-btn pv-btn-secondary" wire:click="closeVariantsModal">Close</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="pv-edit-overlay" wire:click.self="closeEditModal">
            <div class="pv-edit-modal">
                <div class="pv-head">
                    <h3>Edit Variant</h3>
                    <button type="button" class="pv-close" wire:click="closeEditModal">&times;</button>
                </div>
                <div style="flex:1;overflow-y:auto;padding:20px;">
                    <div class="pv-row">
                        <div class="pv-field">
                            <label>SKU</label>
                            <input type="text" wire:model.live="editSku" placeholder="SKU...">
                        </div>
                        <div class="pv-field">
                            <label>Barcode</label>
                            <input type="text" wire:model.live="editBarcode" placeholder="Barcode...">
                        </div>
                    </div>

                    <div class="pv-row">
                        <div class="pv-field">
                            <label>Selling Price Mode</label>
                            <select wire:model.live="editSellingPriceMode">
                                <option value="inherited">Inherit Parent Price</option>
                                <option value="custom">Custom Price</option>
                            </select>
                        </div>
                        @if ($editSellingPriceMode === 'custom')
                            <div class="pv-field">
                                <label>Custom Selling Price</label>
                                <input type="number" wire:model.live="editCustomSellingPrice" placeholder="0.00">
                            </div>
                        @endif
                    </div>

                    <div class="pv-row">
                        <div class="pv-field">
                            <label>Cost Price Mode</label>
                            <select wire:model.live="editCostPriceMode">
                                <option value="inherited">Inherit Parent Cost</option>
                                <option value="custom">Custom Cost</option>
                            </select>
                        </div>
                        @if ($editCostPriceMode === 'custom')
                            <div class="pv-field">
                                <label>Custom Cost Price</label>
                                <input type="number" wire:model.live="editCustomCostPrice" placeholder="0.00">
                            </div>
                        @endif
                    </div>

                    <div class="pv-row">
                        <div class="pv-field">
                            <label>Current Stock</label>
                            <input type="number" wire:model.live="editCurrentStock" placeholder="0">
                        </div>
                        <div class="pv-field">
                            <label>Reorder Level</label>
                            <input type="number" wire:model.live="editReorderLevel" placeholder="0">
                        </div>
                    </div>

                    <div class="pv-row">
                        <div class="pv-field">
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" wire:model.live="editTrackSerialNumbers" style="width:auto;">
                                Track Serial Numbers
                            </label>
                        </div>
                        <div class="pv-field">
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" wire:model.live="editIsActive" style="width:auto;">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="pv-footer">
                    <button type="button" class="pv-btn pv-btn-secondary" wire:click="closeEditModal">Cancel</button>
                    <button type="button" class="pv-btn pv-btn-primary" wire:click="saveVariant">Save Changes</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
