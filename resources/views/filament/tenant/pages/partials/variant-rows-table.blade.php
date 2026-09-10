@php
    $variantRows = $this->generatedVariantRows ?? [];
    $currency = auth()->user()?->tenant?->business?->currency ?? 'UGX';
@endphp

<div
    x-data="{
        get rows() {
            return $wire.generatedVariantRows || [];
        },
        syncRows() {
            $wire.set('generatedVariantRows', JSON.parse(JSON.stringify($wire.generatedVariantRows)));
        },
        updateQty(index, delta) {
            let qty = parseInt($wire.generatedVariantRows[index].quantity || 0) + delta;
            $wire.generatedVariantRows[index].quantity = Math.max(0, qty);
            $wire.set('generatedVariantRows', JSON.parse(JSON.stringify($wire.generatedVariantRows)));
        },
        removeRow(index) {
            let rows = [...$wire.generatedVariantRows];
            rows.splice(index, 1);
            $wire.set('generatedVariantRows', rows);
        },
        updateField(index, field, value) {
            $wire.generatedVariantRows[index][field] = value;
            $wire.set('generatedVariantRows', JSON.parse(JSON.stringify($wire.generatedVariantRows)));
        }
    }"
    x-effect="console.log('Variant rows count:', ($wire.generatedVariantRows || []).length)"
>

    <template x-if="rows && rows.length > 0">
        <div style="margin-top:12px;">
            <style>
                .vr-table { width:100%;border-collapse:collapse;font-size:13px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden; }
                .vr-table th { text-align:left;padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-weight:600;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.04em; }
                .vr-table td { padding:8px 10px;border-bottom:1px solid #f1f5f9; }
                .vr-table input[type="text"], .vr-table input[type="number"] { width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-family:inherit; }
                .vr-table input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,0.15); }
                .vr-qty-group { display:inline-flex;align-items:center;gap:0;border:1px solid #d1d5db;border-radius:6px;overflow:hidden; }
                .vr-qty-group button { width:28px;height:28px;border:none;background:#f1f5f9;cursor:pointer;font-size:14px;font-weight:600;color:#475569; }
                .vr-qty-group button:hover { background:#e2e8f0; }
                .vr-qty-group input { width:40px;text-align:center;border:none;font-size:13px;font-weight:600;padding:4px 0; }
                .vr-qty-group input:focus { outline:none; box-shadow:none; }
                .vr-remove-btn { background:none;border:none;cursor:pointer;color:#dc2626;padding:4px;border-radius:4px; }
                .vr-remove-btn:hover { background:#fee2e2; }
            </style>

            <div style="overflow-x:auto;">
                <table class="vr-table">
                    <thead>
                        <tr>
                            <th style="width:18%;">Variation</th>
                            <th style="width:18%;">Variant Value</th>
                            <th style="width:18%;">SKU</th>
                            <th style="width:12%;">Quantity</th>
                            <th style="width:18%;">Price</th>
                            <th style="width:6%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td>
                                    <input type="text" :value="row.attribute_name" readonly style="background:#f8fafc;color:#64748b;">
                                </td>
                                <td>
                                    <input type="text" :value="row.value_name" readonly style="background:#f8fafc;color:#64748b;">
                                </td>
                                <td>
                                    <input type="text" :value="row.sku" @change="updateField(index, 'sku', $event.target.value)" placeholder="Auto">
                                </td>
                                <td>
                                    <div class="vr-qty-group">
                                        <button type="button" @click="updateQty(index, -1)">-</button>
                                        <input type="number" :value="row.quantity" min="0" readonly>
                                        <button type="button" @click="updateQty(index, 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" :value="row.price" @change="updateField(index, 'price', parseFloat($event.target.value) || 0)" min="0" step="1">
                                </td>
                                <td>
                                    <button type="button" class="vr-remove-btn" @click="removeRow(index)" title="Remove">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
</div>
