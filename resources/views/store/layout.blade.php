<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->store_name ?? 'Online Store' }} — @yield('title', 'Home')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #f5f5f7; color: #1d1d1f; line-height: 1.5; }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }

        .top-bar { background: #1d1d1f; color: #f5f5f7; padding: 8px 0; font-size: .8rem; text-align: center; }
        .top-bar .container { display:flex;justify-content:space-between;align-items:center; }
        .top-bar a { color: #a1a1a6; }
        .top-bar a:hover { color: #fff; }

        .header { background: #fff; border-bottom: 1px solid #e5e5ea; position: sticky; top: 0; z-index: 100; }
        .header .container { display:flex;align-items:center;justify-content:space-between;padding:14px 20px; }
        .header .logo { font-size:1.4rem;font-weight:800;color:#1d1d1f;letter-spacing:-.5px; }
        .header .logo span { color:#6e3efb; }
        .header nav { display:flex;gap:28px;align-items:center; }
        .header nav a { font-size:.9rem;font-weight:500;color:#6e6e73;transition:color .2s; }
        .header nav a:hover { color:#1d1d1f; }
        .header .header-actions { display:flex;gap:16px;align-items:center; }
        .header .search-box { display:flex;align-items:center;background:#f5f5f7;border-radius:10px;padding:0 12px;width:220px; }
        .header .search-box input { border:none;background:transparent;padding:10px 8px;font-size:.85rem;width:100%;outline:none; }
        .header .cart-btn { position:relative;display:flex;align-items:center;gap:6px;padding:8px 16px;background:#6e3efb;color:#fff;border-radius:10px;font-size:.85rem;font-weight:600;transition:background .2s; }
        .header .cart-btn:hover { background:#5a2ed4; }
        .header .cart-badge { background:#ff3b30;color:#fff;font-size:.7rem;font-weight:700;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;position:absolute;top:-4px;right:-4px; }

        .container { max-width:1280px;margin:0 auto;padding:0 20px; }

        .main-layout { display:grid;grid-template-columns:260px 1fr;gap:28px;padding:28px 0; }
        .sidebar { position:sticky;top:80px;height:fit-content; }
        .sidebar-section { background:#fff;border-radius:14px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .sidebar-title { font-size:.8rem;font-weight:700;text-transform:uppercase;color:#1d1d1f;margin-bottom:12px;letter-spacing:.5px; }
        .sidebar-link { display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:8px;font-size:.88rem;color:#48484a;transition:all .15s;cursor:pointer; }
        .sidebar-link:hover { background:#f5f5f7;color:#1d1d1f; }
        .sidebar-link.active { background:#6e3efb;color:#fff;font-weight:600; }
        .sidebar-link .badge { font-size:.75rem;background:#f5f5f7;border-radius:6px;padding:2px 8px;color:#86868b; }
        .sidebar-link.active .badge { background:rgba(255,255,255,.2);color:#fff; }
        .filter-check { display:flex;align-items:center;gap:8px;padding:6px 0;font-size:.88rem;color:#48484a;cursor:pointer; }
        .filter-check input { accent-color:#6e3efb;width:16px;height:16px; }

        .content-area { min-width:0; }
        .content-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px; }
        .content-header h1 { font-size:1.5rem;font-weight:700;color:#1d1d1f; }
        .content-header .result-count { font-size:.9rem;color:#86868b; }
        .sort-bar { display:flex;gap:8px;align-items:center; }
        .sort-btn { padding:8px 16px;border:1px solid #e5e5ea;border-radius:8px;font-size:.85rem;color:#48484a;background:#fff;cursor:pointer;transition:all .15s; }
        .sort-btn:hover { border-color:#6e3efb;color:#6e3efb; }
        .sort-btn.active { background:#6e3efb;color:#fff;border-color:#6e3efb; }
        .view-toggle { display:flex;gap:4px;margin-left:8px; }
        .view-toggle button { width:36px;height:36px;border:1px solid #e5e5ea;border-radius:8px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s; }
        .view-toggle button.active { background:#1d1d1f;color:#fff;border-color:#1d1d1f; }

        .product-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px; }
        .product-card { background:#fff;border-radius:14px;overflow:hidden;transition:transform .2s,box-shadow .2s;position:relative;box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .product-card:hover { transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.08); }
        .product-card .img-wrap { height:220px;background:#f5f5f7;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative; }
        .product-card .img-wrap img { width:100%;height:100%;object-fit:cover;transition:transform .3s; }
        .product-card:hover .img-wrap img { transform:scale(1.05); }
        .product-card .placeholder-img { color:#c7c7cc;font-size:.85rem; }
        .product-card .quick-add { position:absolute;bottom:-50px;left:0;right:0;padding:12px;background:linear-gradient(transparent,rgba(0,0,0,.6));display:flex;justify-content:center;transition:bottom .3s; }
        .product-card:hover .quick-add { bottom:0; }
        .product-card .quick-add button { padding:10px 28px;background:#fff;color:#1d1d1f;border:none;border-radius:10px;font-weight:600;font-size:.85rem;cursor:pointer;transition:background .2s; }
        .product-card .quick-add button:hover { background:#6e3efb;color:#fff; }
        .product-card .info { padding:16px; }
        .product-card .category { font-size:.75rem;color:#86868b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
        .product-card .name { font-weight:600;font-size:.92rem;color:#1d1d1f;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.7em; }
        .product-card .price-row { display:flex;align-items:baseline;gap:8px;margin-top:8px; }
        .product-card .price { font-weight:700;font-size:1.05rem;color:#1d1d1f; }
        .product-card .stock-label { display:inline-block;margin-top:8px;padding:3px 10px;border-radius:6px;font-size:.75rem;font-weight:600; }
        .stock-in { background:#dcfce7;color:#166534; }
        .stock-out { background:#fee2e2;color:#991b1b; }

        .breadcrumb { padding:16px 0;font-size:.85rem;color:#86868b; }
        .breadcrumb a { color:#6e3efb; }
        .breadcrumb a:hover { text-decoration:underline; }

        .footer { background:#1d1d1f;color:#f5f5f7;padding:48px 0 24px;margin-top:60px; }
        .footer-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;margin-bottom:32px; }
        .footer h4 { font-size:.9rem;font-weight:700;margin-bottom:12px;color:#f5f5f7; }
        .footer a { display:block;color:#86868b;font-size:.88rem;padding:3px 0;transition:color .2s; }
        .footer a:hover { color:#f5f5f7; }
        .footer .bottom { border-top:1px solid #424245;padding-top:20px;text-align:center;font-size:.85rem;color:#86868b; }

        .flash { padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:.9rem;font-weight:500; }
        .flash-success { background:#dcfce7;color:#166534;border:1px solid #bbf7d0; }
        .flash-error { background:#fee2e2;color:#991b1b;border:1px solid #fecaca; }

        @media (max-width:768px) {
            .main-layout { grid-template-columns:1fr; }
            .sidebar { position:static; }
            .header nav { display:none; }
            .product-grid { grid-template-columns:repeat(2,1fr);gap:12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <span>Free delivery on orders over {{ number_format($settings->free_delivery_threshold ?? 500000, 0) }} UGX</span>
            <span>📞 {{ $settings->contact_info['phone'] ?? '' }} | ✉️ {{ $settings->contact_info['email'] ?? '' }}</span>
        </div>
    </div>

    <header class="header">
        <div class="container">
                <a href="{{ route('store.home', $slug) }}" class="logo">{{ $settings->store_name ?? 'Store' }}</a>
                <nav>
                    <a href="{{ route('store.home', $slug) }}">Home</a>
                    <a href="{{ route('store.products', $slug) }}">Products</a>
                </nav>
            <div class="header-actions">
                <div class="search-box">
                    <svg width="16" height="16" fill="none" stroke="#86868b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <form action="{{ route('store.products', $slug) }}" method="GET" style="display:flex;width:100%">
                        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="flex:1">
                    </form>
                </div>
                <a href="{{ route('store.cart', $slug) }}" class="cart-btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Cart
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="container" style="padding-top:16px"><div class="flash flash-success">{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="container" style="padding-top:16px"><div class="flash flash-error">{{ session('error') }}</div></div>
    @endif

    @yield('content')

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>{{ $settings->store_name ?? 'Store' }}</h4>
                    <p style="font-size:.88rem;color:#86868b;line-height:1.6">{{ $settings->store_description ?? '' }}</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <a href="{{ route('store.home', $slug) }}">Home</a>
                    <a href="{{ route('store.products', $slug) }}">All Products</a>
                    <a href="{{ route('store.cart', $slug) }}">Cart</a>
                </div>
                @if($settings->contact_info)
                    <div>
                        <h4>Contact</h4>
                        @if(!empty($settings->contact_info['phone']))<a href="tel:{{ $settings->contact_info['phone'] }}">{{ $settings->contact_info['phone'] }}</a>@endif
                        @if(!empty($settings->contact_info['email']))<a href="mailto:{{ $settings->contact_info['email'] }}">{{ $settings->contact_info['email'] }}</a>@endif
                        @if(!empty($settings->contact_info['address']))<a href="#">{{ $settings->contact_info['address'] }}</a>@endif
                    </div>
                @endif
            </div>
            <div class="bottom">&copy; {{ date('Y') }} {{ $settings->store_name ?? 'Store' }}. All rights reserved.</div>
        </div>
    </footer>

    <script>
        fetch('{{ route("store.cart-count", $slug) }}').then(r=>r.json()).then(d=>{document.getElementById('cartCount').textContent=d.count||0;}).catch(()=>{});
    </script>
    @stack('scripts')
</body>
</html>
