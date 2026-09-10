@php
    $mode = $mode ?? 'barcode';
    $isQr = $mode === 'qrcode';
    $currency = $this->currency();
@endphp

<style>
    .pbc-wrap {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .pbc-card {
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 14px;
        background: var(--panel, #ffffff);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .pbc-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
    }

    .pbc-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .pbc-field {
        min-width: 0;
    }

    .pbc-field label {
        display: block;
        margin: 0 0 7px;
        color: var(--text, #0f172a);
        font-size: 13px;
        font-weight: 700;
    }

    .pbc-req {
        color: #dc2626;
        margin-left: 2px;
    }

    .pbc-select,
    .pbc-input {
        width: 100%;
        height: 42px;
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 10px;
        background: #ffffff;
        color: var(--text, #0f172a);
        font-size: 13px;
        padding: 0 12px;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .pbc-select:focus,
    .pbc-input:focus {
        border-color: var(--orange, #ff7a18);
        box-shadow: 0 0 0 3px rgba(255, 122, 24, 0.15);
    }

    .pbc-search {
        position: relative;
    }

    .pbc-search-input {
        position: relative;
    }

    .pbc-search-input svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #94a3b8;
        pointer-events: none;
    }

    .pbc-search-input .pbc-input {
        padding-left: 38px;
    }

    .pbc-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 60;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .pbc-dropdown-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .pbc-dropdown-item:last-child {
        border-bottom: 0;
    }

    .pbc-dropdown-item:hover {
        background: #fff7ed;
    }

    .pbc-dropdown-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
    }

    .pbc-dropdown-code {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.03em;
    }

    .pbc-dropdown-empty {
        padding: 18px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }

    .pbc-avatar {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 10px;
        background: #fff3e6;
        color: #ea580c;
        font-size: 12px;
        font-weight: 800;
    }

    .pbc-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pbc-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 12px;
    }

    .pbc-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    .pbc-table th {
        padding: 12px 16px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        border-bottom: 1px solid #eef0f4;
        white-space: nowrap;
    }

    .pbc-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 13px;
    }

    .pbc-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pbc-product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .pbc-table .pbc-code {
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
    }

    .pbc-stepper {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 9px;
        padding: 3px;
        background: #ffffff;
    }

    .pbc-step-btn {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 7px;
        background: #fff3e6;
        color: #ea580c;
        font-size: 16px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
    }

    .pbc-step-btn:hover {
        background: #ffe4c7;
    }

    .pbc-step-input {
        width: 42px;
        border: 0;
        background: transparent;
        text-align: center;
        color: #0f172a;
        font-size: 13px;
        font-weight: 800;
        -moz-appearance: textfield;
    }

    .pbc-trash {
        width: 32px;
        height: 32px;
        display: inline-grid;
        place-items: center;
        border: 1px solid #fee2e2;
        border-radius: 8px;
        background: #fff;
        color: #dc2626;
        cursor: pointer;
        transition: background .16s ease;
    }

    .pbc-trash:hover {
        background: #fee2e2;
    }

    .pbc-empty {
        text-align: center;
        color: #94a3b8;
        padding: 26px 16px !important;
    }

    .pbc-bottom-row {
        display: grid;
        grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
        gap: 16px;
        align-items: end;
    }

    .pbc-toggles {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 22px;
        padding-bottom: 4px;
    }

    .pbc-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
    }

    .pbc-toggle input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        cursor: pointer;
    }

    .pbc-switch {
        width: 38px;
        height: 21px;
        flex: 0 0 38px;
        position: relative;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .18s ease;
    }

    .pbc-switch::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 17px;
        height: 17px;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        transition: left .18s ease;
    }

    .pbc-toggle input:checked + .pbc-switch {
        background: var(--orange, #ff7a18);
    }

    .pbc-toggle input:checked + .pbc-switch::after {
        left: 19px;
    }

    .pbc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pbc-btn {
        height: 42px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 18px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: background .16s ease, border-color .16s ease, opacity .16s ease;
    }

    .pbc-btn svg {
        width: 16px;
        height: 16px;
    }

    .pbc-btn.primary {
        background: var(--orange, #ff7a18);
        color: #ffffff;
    }

    .pbc-btn.primary:hover {
        background: #ea6a08;
    }

    .pbc-btn.secondary {
        background: #f1f5f9;
        border-color: var(--line, #e5e7eb);
        color: #334155;
    }

    .pbc-btn.secondary:hover {
        background: #e2e8f0;
    }

    .pbc-btn.danger {
        background: #fee2e2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .pbc-btn.danger:hover {
        background: #fecaca;
    }

    .pbc-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 30px 20px;
        overflow-y: auto;
        background: rgba(15, 23, 42, 0.55);
    }

    .pbc-modal {
        width: 100%;
        max-width: 880px;
        border-radius: 16px;
        background: var(--panel, #ffffff);
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.35);
        overflow: hidden;
    }

    .pbc-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #eef0f4;
    }

    .pbc-modal-head h4 {
        margin: 0;
        color: var(--text, #0f172a);
        font-size: 17px;
        font-weight: 800;
    }

    .pbc-close {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
    }

    .pbc-close:hover {
        background: #fecaca;
    }

    .pbc-modal-body {
        padding: 20px;
    }

    .pbc-print-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .pbc-labels-grid {
        display: grid;
        gap: 10px;
    }

    .pbc-label {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 12px 10px;
        text-align: center;
        background: #ffffff;
        min-width: 0;
    }

    .pbc-lstore {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pbc-lname {
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pbc-lprice {
        color: #ea580c;
        font-size: 12px;
        font-weight: 800;
        margin-top: 2px;
    }

    .pbc-lbarcode {
        display: flex;
        justify-content: center;
        margin-top: 8px;
    }

    .pbc-lbarcode svg {
        display: block;
    }

    .pbc-lcode {
        margin-top: 5px;
        color: #334155;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
    }

    .pbc-lqr img {
        display: block;
        width: 104px;
        height: 104px;
        margin: 8px auto 0;
    }

    .pbc-lref {
        margin-top: 6px;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    @media (max-width: 820px) {
        .pbc-row,
        .pbc-bottom-row {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        body {
            background: #ffffff !important;
        }

        body * {
            visibility: hidden;
        }

        #print-region,
        #print-region * {
            visibility: visible;
        }

        #print-region {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
        }

        .pbc-overlay {
            position: static !important;
            padding: 0 !important;
            background: #ffffff !important;
        }

        .pbc-modal {
            max-width: 100% !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .pbc-modal-head,
        .pbc-print-actions,
        .pbc-close {
            display: none !important;
        }

        .pbc-label {
            break-inside: avoid;
        }
    }
</style>

<div class="pbc-wrap">
    <div class="pbc-card">
        <div class="pbc-form">
            <div class="pbc-row">
                <div class="pbc-field">
                    <label>Category <span class="pbc-req">*</span></label>
                    <select class="pbc-select" wire:model="categoryId">
                        <option value="">Select</option>
                        @foreach ($this->categories() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pbc-field">
                    <label>Brand <span class="pbc-req">*</span></label>
                    <select class="pbc-select" wire:model="brandId">
                        <option value="">Select</option>
                        @foreach ($this->brands() as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pbc-field pbc-search">
                <label>Product <span class="pbc-req">*</span></label>
                <div class="pbc-search-input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input class="pbc-input" type="text" wire:model.live.debounce.250ms="search" placeholder="Search Product by Code" />
                </div>

                @if ($search !== '')
                    <div class="pbc-dropdown">
                        @forelse ($this->searchResults() as $result)
                            <button type="button" class="pbc-dropdown-item" wire:click="addProduct('{{ $result['id'] }}')">
                                <span class="pbc-avatar">{{ strtoupper(substr($result['name'], 0, 2)) }}</span>
                                <span class="pbc-dropdown-name">{{ $result['name'] }}</span>
                                <span class="pbc-dropdown-code">{{ $result['code'] }}</span>
                            </button>
                        @empty
                            <div class="pbc-dropdown-empty">No products found</div>
                        @endforelse
                    </div>
                @endif
            </div>

            <div class="pbc-table-wrap">
                <table class="pbc-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Code</th>
                            @if ($isQr)
                                <th>Reference Number</th>
                            @endif
                            <th>Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    <div class="pbc-product-cell">
                                        @if (! empty($item['image']))
                                            <span class="pbc-avatar"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" /></span>
                                        @else
                                            <span class="pbc-avatar">{{ strtoupper(substr($item['name'], 0, 2)) }}</span>
                                        @endif
                                        <span>{{ $item['name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $item['sku'] }}</td>
                                <td class="pbc-code">{{ $item['code'] }}</td>
                                @if ($isQr)
                                    <td>{{ $item['ref'] }}</td>
                                @endif
                                <td>
                                    <div class="pbc-stepper">
                                        <button type="button" class="pbc-step-btn" wire:click="decrementQty('{{ $item['id'] }}')">&minus;</button>
                                        <input class="pbc-step-input" type="text" value="{{ $item['qty'] }}" readonly />
                                        <button type="button" class="pbc-step-btn" wire:click="incrementQty('{{ $item['id'] }}')">+</button>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="pbc-trash" wire:click="removeProduct('{{ $item['id'] }}')" title="Remove">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isQr ? 6 : 5 }}" class="pbc-empty">
                                    No products selected &mdash; search and pick products above
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pbc-bottom-row">
                <div class="pbc-field">
                    <label>Paper Size <span class="pbc-req">*</span></label>
                    <select class="pbc-select" wire:model="paperSize">
                        @foreach ($this->paperSizes() as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pbc-toggles">
                    @if ($isQr)
                        <label class="pbc-toggle">
                            <input type="checkbox" wire:model="showReference" />
                            <span class="pbc-switch"></span>
                            <span>Reference Number</span>
                        </label>
                    @else
                        <label class="pbc-toggle">
                            <input type="checkbox" wire:model="showStoreName" />
                            <span class="pbc-switch"></span>
                            <span>Show Store Name</span>
                        </label>
                        <label class="pbc-toggle">
                            <input type="checkbox" wire:model="showProductName" />
                            <span class="pbc-switch"></span>
                            <span>Show Product Name</span>
                        </label>
                        <label class="pbc-toggle">
                            <input type="checkbox" wire:model="showPrice" />
                            <span class="pbc-switch"></span>
                            <span>Show Price</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="pbc-actions">
                <button type="button" class="pbc-btn primary" wire:click="generate">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    {{ $isQr ? 'Generate QR Code' : 'Generate Barcode' }}
                </button>
                <button type="button" class="pbc-btn secondary" wire:click="resetAll">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    {{ $isQr ? 'Reset' : 'Reset Barcode' }}
                </button>
                <button type="button" class="pbc-btn danger" wire:click="generate">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                    {{ $isQr ? 'Print QRcode' : 'Print Barcode' }}
                </button>
            </div>
        </div>
    </div>
</div>

@if ($previewOpen)
    <div class="pbc-overlay" wire:key="preview-modal">
        <div class="pbc-modal">
            <div class="pbc-modal-head">
                <h4>{{ $isQr ? 'QR Code' : 'Barcode' }}</h4>
                <button type="button" class="pbc-close" wire:click="closePreview" title="Close">&times;</button>
            </div>
            <div class="pbc-modal-body">
                <div id="print-region">
                    <div class="pbc-print-actions">
                        <button type="button" class="pbc-btn primary" onclick="window.print()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                            Print {{ $isQr ? 'QR Code' : 'Barcode' }}
                        </button>
                    </div>

                    <div class="pbc-labels-grid" style="grid-template-columns: repeat({{ $this->labelCols() }}, 1fr);">
                        @forelse ($items as $item)
                            @for ($i = 0; $i < $item['qty']; $i++)
                                @if ($isQr)
                                    <div class="pbc-label">
                                        @if ($showStoreName)
                                            <div class="pbc-lstore">{{ $this->storeName() }}</div>
                                        @endif
                                        @if ($showProductName)
                                            <div class="pbc-lname">{{ $item['name'] }}</div>
                                        @endif
                                        <div class="pbc-lqr">
                                            <img src="{{ $this->qrSvg($item['code']) }}" alt="QR" />
                                        </div>
                                        @if ($showReference)
                                            <div class="pbc-lref">Ref No : {{ $item['ref'] }}</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="pbc-label">
                                        @if ($showStoreName)
                                            <div class="pbc-lstore">{{ $this->storeName() }}</div>
                                        @endif
                                        @if ($showProductName)
                                            <div class="pbc-lname">{{ $item['name'] }}</div>
                                        @endif
                                        @if ($showPrice)
                                            <div class="pbc-lprice">Price: {{ $currency }} {{ number_format($item['price'], 0) }}</div>
                                        @endif
                                        <div class="pbc-lbarcode">{!! $this->barcodeSvg($item['code'], 46) !!}</div>
                                        <div class="pbc-lcode">{{ $item['code'] }}</div>
                                    </div>
                                @endif
                            @endfor
                        @empty
                            <div class="pbc-empty">No products selected</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
