<div
    class="scanner-input-wrap"
    x-data="{
        listening: false,
        buffer: '',
        timer: null,
        init() {
            // Physical scanner detection: rapid keystrokes + Enter
            document.addEventListener('keydown', (e) => {
                if (e.target.closest('.scanner-input-field') || e.target.closest('input, textarea, select')) return;

                if (e.key === 'Enter' && this.buffer.length >= 4) {
                    e.preventDefault();
                    $wire.processScan(this.buffer);
                    this.buffer = '';
                    clearTimeout(this.timer);
                    return;
                }

                if (e.key.length === 1) {
                    this.buffer += e.key;
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => { this.buffer = ''; }, 100);
                }
            });
        }
    }"
>
    {{-- Scanner input field --}}
    <div class="scanner-field-row" style="display:flex;gap:8px;align-items:center;">
        <div style="flex:1;position:relative;">
            <input
                type="text"
                wire:model.live.debounce.300ms="scanInput"
                wire:keydown.enter="processManualInput"
                class="scanner-input-field"
                placeholder="Scan barcode or serial number..."
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                @if($autoFocus) autofocus @endif
                style="
                    width:100%;
                    padding:10px 14px 10px 38px;
                    border:2px solid #e5e7eb;
                    border-radius:10px;
                    font-size:14px;
                    font-weight:500;
                    background:#fff;
                    color:#1e293b;
                    outline:none;
                    transition:border-color .2s, box-shadow .2s;
                "
            >
            <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
        </div>

        <button
            type="button"
            wire:click="toggleCamera"
            style="
                height:40px;
                padding:0 14px;
                border:2px solid #e5e7eb;
                border-radius:10px;
                background:#fff;
                color:#374151;
                font-size:13px;
                font-weight:600;
                cursor:pointer;
                display:flex;
                align-items:center;
                gap:6px;
                white-space:nowrap;
            "
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                <circle cx="12" cy="13" r="3"/>
            </svg>
            Camera
        </button>
    </div>

    {{-- Camera modal --}}
    @if($showCamera)
    <div
        x-data="{
            scanning: false,
            initCamera() {
                if (typeof Html5Qrcode === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                    script.onload = () => this.startScan();
                    document.head.appendChild(script);
                } else {
                    this.startScan();
                }
            },
            startScan() {
                this.scanning = true;
                const el = document.getElementById('camera-reader');
                if (!el) return;
                const scanner = new Html5Qrcode('camera-reader');
                scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 150 } },
                    (decodedText) => {
                        $wire.processScan(decodedText);
                        scanner.stop();
                        this.scanning = false;
                        $wire.set('showCamera', false);
                    },
                    () => {}
                );
            }
        }"
        x-init="initCamera()"
        style="
            position:fixed;inset:0;z-index:99999;
            background:rgba(0,0,0,.6);
            display:flex;align-items:center;justify-content:center;
        "
        @click.self="$wire.set('showCamera', false)"
    >
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:400px;width:90%;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-weight:700;font-size:15px;color:#1e293b;">Scan Barcode</span>
                <button
                    type="button"
                    @click="$wire.set('showCamera', false)"
                    style="border:none;background:none;cursor:pointer;color:#64748b;font-size:18px;"
                >&times;</button>
            </div>
            <div id="camera-reader" style="border-radius:10px;overflow:hidden;"></div>
            <p style="text-align:center;color:#94a3b8;font-size:12px;margin-top:8px;">Point camera at barcode</p>
        </div>
    </div>
    @endif

    {{-- Scan result feedback --}}
    @if($lastScannedMessage)
    <div
        style="
            margin-top:8px;
            padding:8px 12px;
            border-radius:8px;
            font-size:13px;
            font-weight:500;
            display:flex;
            align-items:center;
            gap:6px;
            animation:fadeIn .2s ease;
        "
        @if($lastScanStatus === 'success') style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;" @endif
        @if($lastScanStatus === 'error') style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;" @endif
        @if($lastScanStatus === 'warning') style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;" @endif
    >
        @if($lastScanStatus === 'success')
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
        @elseif($lastScanStatus === 'error')
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        @endif
        {{ $lastScannedMessage }}
    </div>
    @endif
</div>
