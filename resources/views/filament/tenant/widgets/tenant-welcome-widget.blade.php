<x-filament-widgets::widget>
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h1 style="margin:0; font-size:26px; line-height:1.2; font-weight:700; color:#1F2937;">Welcome, {{ $firstName }}</h1>
            <p style="margin:6px 0 0; font-size:14px; color:#6B7280; font-weight:500;">
                You have <span style="color:#0D6EFD; font-weight:700;">{{ $todayOrders }}+</span> Orders, Today
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #E9EDF2; border-radius:8px; padding:8px 14px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <span style="font-size:13px; color:#6B7280;">{{ $dateRange }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #E9EDF2; border-radius:8px; padding:8px 14px; color:#6B7280; font-size:13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                Search Product
            </div>
        </div>
    </div>

    @if ($lowStockProduct)
        <div id="low-stock-alert" style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:#FFF4E6; border:1px solid #FFDDB8; border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:#8A4B0D;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <span>Your Product <b style="color:#E04F16;">{{ $lowStockProduct->name }}</b> is running Low, already below
                    <span style="color:#E04F16; font-weight:600;">{{ $lowStockProduct->reorder_level }} Pcs</span>.
                    <a href="/tenant/stock-dashboard" style="color:#E04F16; text-decoration:underline; font-weight:600; margin-left:4px;">Add Stock</a>
                </span>
            </div>
            <button onclick="document.getElementById('low-stock-alert').style.display='none'" style="background:none; border:none; color:#B07A3B; cursor:pointer; font-size:16px; line-height:1;">&times;</button>
        </div>
    @endif
</x-filament-widgets::widget>