<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HALIS - Modern POS & Inventory Management System</title>
    <meta name="description" content="HALIS is a modern POS & Inventory Management system for Retail, Restaurant, and Multi-Store businesses. Built with Laravel, Filament, and Livewire.">
    @include('partials.pwa')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200..1000&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Nunito Sans', sans-serif; color: #1a1a2e; line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; transition: color .2s; }
        img { max-width: 100%; display: block; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* ======================== HEADER ======================== */
        .header { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(255,255,255,.97); backdrop-filter: blur(12px); border-bottom: 1px solid #f0f0f5; transition: box-shadow .3s; }
        .header.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,.08); }
        .header .container { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
        .logo .dot { width: 10px; height: 10px; border-radius: 50%; background: #ff9f43; display: inline-block; }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a { font-size: .9rem; font-weight: 600; color: #4a4a68; transition: color .2s; }
        .nav-links a:hover { color: #ff9f43; }
        .header-btns { display: flex; gap: 12px; align-items: center; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: .9rem; border: none; cursor: pointer; transition: all .2s; font-family: inherit; }
        .btn-primary { background: #ff9f43; color: #fff; }
        .btn-primary:hover { background: #e8903a; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255,159,67,.35); }
        .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; }
        .btn-outline:hover { border-color: #ff9f43; color: #ff9f43; }
        .btn-white { background: #fff; color: #1a1a2e; }
        .btn-white:hover { background: #f9fafb; }
        .btn-lg { padding: 14px 32px; font-size: 1rem; border-radius: 10px; }

        /* ======================== HERO ======================== */
        .hero { padding: 140px 0 80px; background: #FF9F43; color: #fff; }
        .hero .container { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid rgba(255,255,255,.3); border-radius: 50px; padding: 6px 16px; font-size: .82rem; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
        .hero-badge .fire { font-size: 1rem; }
        .hero h1 { font-size: 3rem; font-weight: 800; line-height: 1.15; color: #1a1a2e; margin-bottom: 20px; }
        .hero h1 span { color: #fff; }
        .hero p { font-size: 1.05rem; color: rgba(26,26,46,.8); line-height: 1.7; margin-bottom: 28px; max-width: 520px; }
        .hero-stats { display: flex; gap: 32px; margin-bottom: 28px; flex-wrap: wrap; }
        .hero-stats .stat { text-align: center; }
        .hero-stats .stat strong { display: block; font-size: 1.8rem; font-weight: 800; color: #1a1a2e; }
        .hero-stats .stat span { font-size: .82rem; color: rgba(26,26,46,.6); font-weight: 600; }
        .hero-badges { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 32px; }
        .hero-badges li { list-style: none; display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid rgba(255,255,255,.3); border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 600; color: #1a1a2e; }
        .hero-badges li .check { color: #1a1a2e; }
        .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 24px; }
        .hero-rating { display: flex; align-items: center; gap: 8px; font-size: .9rem; }
        .hero-rating .stars { color: #1a1a2e; font-size: 1rem; }
        .hero-rating strong { color: #1a1a2e; }
        .hero-img { position: relative; }
        .hero-img .dashboard-img { border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.12); }
        .hero-img .float-card { position: absolute; background: #fff; border-radius: 12px; padding: 14px 18px; box-shadow: 0 8px 30px rgba(0,0,0,.1); font-size: .85rem; font-weight: 600; }
        .hero-img .float-card.top { top: 10%; right: -20px; }
        .hero-img .float-card.bottom { bottom: 15%; left: -30px; }

        /* ======================== SECTIONS ======================== */
        section { padding: 80px 0; }
        .section-header { text-align: center; margin-bottom: 48px; }
        .section-header .tag { display: inline-flex; align-items: center; gap: 6px; background: #fff4e6; color: #ff9f43; padding: 6px 16px; border-radius: 50px; font-size: .82rem; font-weight: 700; margin-bottom: 14px; }
        .section-header h2 { font-size: 2.2rem; font-weight: 800; color: #1a1a2e; margin-bottom: 14px; }
        .section-header h2 span { color: #ff9f43; }
        .section-header p { font-size: 1rem; color: #8686a0; max-width: 600px; margin: 0 auto; }

        /* ======================== WHY CHOOSE ======================== */
        .why-section { background: #f8f9fc; }
        .why-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .why-card { background: #fff; border-radius: 14px; padding: 28px; text-align: center; transition: transform .2s, box-shadow .2s; border: 1px solid #f0f0f5; }
        .why-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.06); }
        .why-icon { width: 56px; height: 56px; border-radius: 14px; background: #fff4e6; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 1.5rem; }
        .why-card h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 8px; color: #1a1a2e; }
        .why-card p { font-size: .88rem; color: #8686a0; line-height: 1.6; }

        /* ======================== FEATURES ======================== */
        .features-section { background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%); color: #fff; position: relative; overflow: hidden; }
        .features-section::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(255,159,67,.12) 0%, transparent 70%); border-radius: 50%; }
        .features-section::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 200px; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat; opacity: 1; z-index: 0; }
        .features-section .section-header h2 { color: #fff; }
        .features-section .section-header h2 span { color: #ff9f43; }
        .features-section .section-header p { color: #a0a0c0; }
        .features-section .section-header .tag { background: rgba(255,159,67,.15); }
        .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; z-index: 1; }
        .feature-card { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 14px; padding: 28px; transition: transform .2s, background .2s; }
        .feature-card:hover { transform: translateY(-4px); background: rgba(255,255,255,.1); }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(255,159,67,.15); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; font-size: 1.3rem; color: #ff9f43; }
        .feature-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; color: #fff; }
        .feature-card p { font-size: .85rem; color: #a0a0c0; line-height: 1.6; }

        /* ======================== DEMOS ======================== */
        .demos-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 28px; }
        .demo-card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #f0f0f5; transition: transform .2s, box-shadow .2s; }
        .demo-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.08); }
        .demo-img { height: 260px; background: #f5f6fa; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        .demo-img .placeholder { color: #c7c7cc; font-size: 1rem; }
        .demo-info { padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .demo-info h3 { font-size: 1.1rem; font-weight: 700; }
        .demo-info .link { color: #ff9f43; font-weight: 700; font-size: .9rem; display: flex; align-items: center; gap: 4px; }

        /* ======================== MODULES ======================== */
        .modules-section { background: #f8f9fc; }
        .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .module-card { background: #fff; border-radius: 14px; padding: 24px; border: 1px solid #f0f0f5; display: flex; gap: 16px; align-items: flex-start; transition: transform .2s, box-shadow .2s; }
        .module-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.06); }
        .module-num { width: 40px; height: 40px; border-radius: 10px; background: #fff4e6; color: #ff9f43; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .9rem; flex-shrink: 0; }
        .module-card h3 { font-size: .95rem; font-weight: 700; margin-bottom: 4px; }
        .module-card p { font-size: .83rem; color: #8686a0; }

        /* ======================== STATS ======================== */
        .stats-section { background: linear-gradient(135deg, #ff9f43 0%, #ff7b2e 100%); color: #fff; padding: 60px 0; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; text-align: center; }
        .stat-item strong { display: block; font-size: 2.5rem; font-weight: 800; }
        .stat-item span { font-size: .9rem; opacity: .85; font-weight: 600; }

        /* ======================== CTA ======================== */
        .cta-section { padding: 80px 0; text-align: center; }
        .cta-box { background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%); border-radius: 20px; padding: 60px 40px; color: #fff; }
        .cta-box h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 16px; }
        .cta-box h2 span { color: #ff9f43; }
        .cta-box p { font-size: 1.05rem; color: #a0a0c0; margin-bottom: 32px; max-width: 550px; margin-left: auto; margin-right: auto; }
        .cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        /* ======================== FOOTER ======================== */
        .footer { background: #1a1a2e; color: #a0a0c0; padding: 60px 0 24px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .footer-brand .logo { color: #fff; margin-bottom: 12px; }
        .footer-brand p { font-size: .88rem; line-height: 1.7; }
        .footer h4 { color: #fff; font-size: .95rem; font-weight: 700; margin-bottom: 16px; }
        .footer a { display: block; color: #a0a0c0; font-size: .88rem; padding: 4px 0; transition: color .2s; }
        .footer a:hover { color: #ff9f43; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,.08); padding-top: 20px; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; }
        .footer-bottom .socials { display: flex; gap: 12px; }
        .footer-bottom .socials a { width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,.06); display: flex; align-items: center; justify-content: center; font-size: .9rem; padding: 0; }
        .footer-bottom .socials a:hover { background: #ff9f43; color: #fff; }

        /* ======================== RESPONSIVE ======================== */
        @media (max-width: 991px) {
            .hero .container { grid-template-columns: 1fr; text-align: center; }
            .hero p { margin: 0 auto 28px; }
            .hero-stats { justify-content: center; }
            .hero-badges { justify-content: center; }
            .hero-btns { justify-content: center; }
            .hero-rating { justify-content: center; }
            .hero-img { max-width: 500px; margin: 0 auto; }
            .why-grid, .features-grid { grid-template-columns: repeat(2, 1fr); }
            .demos-grid { grid-template-columns: 1fr; }
            .modules-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .nav-links { display: none; }
        }
        @media (max-width: 576px) {
            .hero h1 { font-size: 2rem; }
            .section-header h2 { font-size: 1.6rem; }
            .why-grid, .features-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
        }

        /* ======================== MOBILE MENU ======================== */
        .mobile-toggle { display: none; background: none; border: none; cursor: pointer; padding: 8px; }
        .mobile-toggle span { display: block; width: 24px; height: 2px; background: #1a1a2e; margin: 5px 0; border-radius: 2px; transition: .3s; }
        @media (max-width: 991px) {
            .mobile-toggle { display: block; }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header" id="header">
        <div class="container">
            <a href="#" class="logo"><span class="dot"></span> HALIS</a>
            <nav class="nav-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#demos">Demos</a>
                <a href="#modules">Modules</a>
                <a href="#pricing">Pricing</a>
                <a href="#faq">FAQ</a>
            </nav>
            <div class="header-btns">
                <a href="/tenant" class="btn btn-outline">Login</a>
                <a href="/register" class="btn btn-primary">Get Started</a>
            </div>
            <button class="mobile-toggle" onclick="document.querySelector('.nav-links').classList.toggle('show')">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge"><span class="fire">🔥</span> #1 Modern POS & Inventory Management System</div>
                <h1>Launch a <span>POS & Inventory Dashboard</span> in Days, Not Months</h1>
                <p>HALIS is a production-ready POS system for Retail, Restaurant, and Multi-Store businesses — built with Laravel, Filament & Livewire, featuring real-time inventory, multi-branch management, and an integrated online shop.</p>
                <div class="hero-stats">
                    <div class="stat"><strong>100+</strong><span>Features</span></div>
                    <div class="stat"><strong>50+</strong><span>Pages</span></div>
                    <div class="stat"><strong>Multi</strong><span>Branch</span></div>
                    <div class="stat"><strong>Online</strong><span>Shop</span></div>
                </div>
                <ul class="hero-badges">
                    <li><span class="check">✓</span> Multi-Branch Inventory</li>
                    <li><span class="check">✓</span> Online Storefront</li>
                    <li><span class="check">✓</span> Real-Time Stock Tracking</li>
                    <li><span class="check">✓</span> Loyalty & Wallet</li>
                </ul>
                <div class="hero-btns">
                    <a href="/register" class="btn btn-primary btn-lg">🚀 Get Started Free</a>
                    <a href="#features" class="btn btn-outline btn-lg">Explore Features</a>
                </div>
                <div class="hero-rating">
                    <span class="stars">★★★★★</span>
                    <span><strong>4.9/5</strong> from early users</span>
                </div>
            </div>
            <div class="hero-img">
                <img src="{{ url('images/dashboard-preview.png') }}" alt="HALIS Dashboard" style="border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.18);width:100%;height:auto">
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE -->
    <section class="why-section" id="why">
        <div class="container">
            <div class="section-header">
                <div class="tag">Why HALIS</div>
                <h2>Why Businesses Choose <span>HALIS</span></h2>
                <p>Built for real businesses that need reliable POS, inventory, and multi-branch management.</p>
            </div>
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon">⚡</div>
                    <h3>Lightning Fast Checkout</h3>
                    <p>Process sales in seconds with barcode scanning, quick product search, and one-tap payment processing.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">🏪</div>
                    <h3>Multi-Branch Management</h3>
                    <p>Manage unlimited branches from one dashboard. Transfer stock, compare performance, and centralize operations.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">📦</div>
                    <h3>Real-Time Inventory</h3>
                    <p>Track stock levels across branches in real-time. Get low-stock alerts, manage serial numbers, and automate reorder points.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">🌐</div>
                    <h3>Online Storefront</h3>
                    <p>Built-in online shop that syncs with your POS inventory. Customers can browse, order, and track deliveries.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <div class="tag">Core Features</div>
                <h2>Everything You Need to <span>Run Your Business</span></h2>
                <p>From point-of-sale to accounting, HALIS covers every aspect of your retail operations.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>POS Terminal</h3>
                    <p>Touch-optimized checkout with product grid, variant support, and multiple payment methods.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sales Analytics</h3>
                    <p>Real-time dashboards with revenue tracking, top products, and branch comparison reports.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏷️</div>
                    <h3>Product Management</h3>
                    <p>Manage products with variants, barcodes, sale units, images, and batch/serial tracking.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Customer CRM</h3>
                    <p>Customer profiles, purchase history, credit management, loyalty points, and digital wallets.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Purchase Orders</h3>
                    <p>Create POs, track deliveries, manage suppliers, and update stock automatically on receipt.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Stock Transfers</h3>
                    <p>Transfer inventory between branches with approval workflows and real-time tracking.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h3>Invoices & Receipts</h3>
                    <p>Generate professional invoices, receipts, and quotations with customizable templates.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👤</div>
                    <h3>User Roles & Permissions</h3>
                    <p>Granular access control with role-based permissions and branch-level restrictions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DEMOS -->
    <section class="demos-section" id="demos" style="padding:80px 0">
        <div class="container">
            <div class="section-header">
                <div class="tag">Live Demos</div>
                <h2>Built for <span>Every Industry</span></h2>
                <p>Whether you run a retail shop, restaurant, or multi-branch chain — HALIS adapts to your business.</p>
            </div>
            <div class="demos-grid">
                <div class="demo-card">
                    <div class="demo-img" style="background:#f5f6fa">
                        <img src="{{ url('images/retail-pos.jpg') }}" alt="Retail POS" style="width:100%;height:100%;object-fit:cover">
                    </div>
                    <div class="demo-info">
                        <h3>Retail POS Dashboard</h3>
                        <a href="/tenant" class="link">View Demo →</a>
                    </div>
                </div>
                <div class="demo-card">
                    <div class="demo-img" style="background:#f5f6fa">
                        <img src="{{ url('images/restaurant-pos.jpg') }}" alt="Restaurant POS" style="width:100%;height:100%;object-fit:cover">
                    </div>
                    <div class="demo-info">
                        <h3>Restaurant POS Dashboard</h3>
                        <a href="/tenant" class="link">View Demo →</a>
                    </div>
                </div>
                <div class="demo-card">
                    <div class="demo-img" style="background:#f5f6fa">
                        <img src="{{ url('images/multi-store.jpg') }}" alt="Multi-Store" style="width:100%;height:100%;object-fit:cover">
                    </div>
                    <div class="demo-info">
                        <h3>Multi-Branch Management</h3>
                        <a href="/tenant" class="link">View Demo →</a>
                    </div>
                </div>
                <div class="demo-card">
                    <div class="demo-img" style="background:#f5f6fa">
                        <img src="{{ url('images/online-shop.jpg') }}" alt="Online Shop" style="width:100%;height:100%;object-fit:cover">
                    </div>
                    <div class="demo-info">
                        <h3>Online Storefront</h3>
                        <a href="/nova-retail-hub" class="link">View Demo →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MODULES -->
    <section class="modules-section" id="modules">
        <div class="container">
            <div class="section-header">
                <div class="tag">Modules</div>
                <h2>Powerful <span>Business Modules</span></h2>
                <p>Every module is designed to work together seamlessly for complete business management.</p>
            </div>
            <div class="modules-grid">
                <div class="module-card">
                    <div class="module-num">01</div>
                    <div><h3>Point of Sale</h3><p>Touch-screen checkout with barcode scanning, split payments, and receipt printing.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">02</div>
                    <div><h3>Inventory Management</h3><p>Stock tracking, low-stock alerts, stock transfers, and audit trails.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">03</div>
                    <div><h3>Purchase Management</h3><p>Supplier management, purchase orders, receiving, and cost tracking.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">04</div>
                    <div><h3>Customer Management</h3><p>Customer profiles, purchase history, credit accounts, and loyalty program.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">05</div>
                    <div><h3>Reports & Analytics</h3><p>Sales reports, profit & loss, tax reports, and branch comparisons.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">06</div>
                    <div><h3>Multi-Branch</h3><p>Branch management, stock transfers, and centralized reporting.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">07</div>
                    <div><h3>Online Shop</h3><p>Public storefront with cart, checkout, and order fulfillment.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">08</div>
                    <div><h3>Loyalty & Wallet</h3><p>Points system, customer wallets, and cross-branch credit.</p></div>
                </div>
                <div class="module-card">
                    <div class="module-num">09</div>
                    <div><h3>Email Integration</h3><p>Send invoices, receipts, and notifications via integrated email.</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item"><strong>100+</strong><span>Features Built</span></div>
                <div class="stat-item"><strong>50+</strong><span>Pages & Components</span></div>
                <div class="stat-item"><strong>Multi</strong><span>Branch Support</span></div>
                <div class="stat-item"><strong>24/7</strong><span>Access Anywhere</span></div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section style="padding:80px 0;background:#f8f9fc" id="testimonials">
        <div class="container">
            <div class="section-header">
                <div class="tag">Testimonials</div>
                <h2>What Our <span>Users Say</span></h2>
                <p>Trusted by businesses managing inventory, sales, and multi-branch operations every day.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px">
                <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #f0f0f5">
                    <div style="display:flex;gap:4px;color:#ff9f43;font-size:1rem;margin-bottom:14px">★★★★★</div>
                    <p style="font-size:.92rem;color:#4a4a68;line-height:1.7;margin-bottom:20px">"HALIS transformed how we manage our 3 branches. Stock transfers that used to take hours now happen in minutes. The real-time dashboard is a game changer."</p>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:42px;height:42px;border-radius:50%;background:#ff9f43;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem">JK</div>
                        <div>
                            <p style="font-weight:700;font-size:.9rem">James K.</p>
                            <p style="font-size:.8rem;color:#8686a0">Retail Business Owner</p>
                        </div>
                    </div>
                </div>
                <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #f0f0f5">
                    <div style="display:flex;gap:4px;color:#ff9f43;font-size:1rem;margin-bottom:14px">★★★★★</div>
                    <p style="font-size:.92rem;color:#4a4a68;line-height:1.7;margin-bottom:20px">"The online storefront integration is seamless. Customers can order online and we fulfill from the nearest branch. Sales increased 30% in the first month."</p>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:42px;height:42px;border-radius:50%;background:#6e3efb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem">SA</div>
                        <div>
                            <p style="font-weight:700;font-size:.9rem">Sarah A.</p>
                            <p style="font-size:.8rem;color:#8686a0">Restaurant Manager</p>
                        </div>
                    </div>
                </div>
                <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #f0f0f5">
                    <div style="display:flex;gap:4px;color:#ff9f43;font-size:1rem;margin-bottom:14px">★★★★★</div>
                    <p style="font-size:.92rem;color:#4a4a68;line-height:1.7;margin-bottom:20px">"Finally a POS that handles multi-branch inventory properly. The loyalty program and customer wallet features keep our customers coming back."</p>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:42px;height:42px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem">DM</div>
                        <div>
                            <p style="font-weight:700;font-size:.9rem">David M.</p>
                            <p style="font-size:.8rem;color:#8686a0">Supermarket Chain</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section style="padding:80px 0" id="faq">
        <div class="container">
            <div class="section-header">
                <div class="tag">FAQ</div>
                <h2>Frequently Asked <span>Questions</span></h2>
                <p>Everything you need to know about HALIS.</p>
            </div>
            <div style="max-width:720px;margin:0 auto">
                <details style="background:#fff;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:12px;overflow:hidden">
                    <summary style="padding:18px 24px;font-weight:700;font-size:.95rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center">
                        What is HALIS?
                        <span style="font-size:1.2rem;color:#ff9f43">+</span>
                    </summary>
                    <div style="padding:0 24px 18px;font-size:.9rem;color:#4a4a68;line-height:1.7">HALIS is a modern POS and Inventory Management system built with Laravel, Filament, and Livewire. It includes point-of-sale, inventory tracking, multi-branch management, an online storefront, loyalty programs, and more.</div>
                </details>
                <details style="background:#fff;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:12px;overflow:hidden">
                    <summary style="padding:18px 24px;font-weight:700;font-size:.95rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center">
                        How many branches can I manage?
                        <span style="font-size:1.2rem;color:#ff9f43">+</span>
                    </summary>
                    <div style="padding:0 24px 18px;font-size:.9rem;color:#4a4a68;line-height:1.7">The Business plan supports unlimited branches. Each branch has its own stock levels, sales tracking, and staff permissions — all managed from a single dashboard.</div>
                </details>
                <details style="background:#fff;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:12px;overflow:hidden">
                    <summary style="padding:18px 24px;font-weight:700;font-size:.95rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center">
                        Does it include an online shop?
                        <span style="font-size:1.2rem;color:#ff9f43">+</span>
                    </summary>
                    <div style="padding:0 24px 18px;font-size:.9rem;color:#4a4a68;line-height:1.7">Yes. HALIS includes a built-in online storefront where customers can browse products, add to cart, checkout, and track orders. It syncs with your POS inventory in real-time.</div>
                </details>
                <details style="background:#fff;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:12px;overflow:hidden">
                    <summary style="padding:18px 24px;font-weight:700;font-size:.95rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center">
                        What technology stack is used?
                        <span style="font-size:1.2rem;color:#ff9f43">+</span>
                    </summary>
                    <div style="padding:0 24px 18px;font-size:.9rem;color:#4a4a68;line-height:1.7">HALIS is built with Laravel 12, Filament 5.7, Livewire 4.4, and PostgreSQL. It uses inline styles (no Tailwind dependency) and runs on any standard hosting with PHP 8.2+.</div>
                </details>
                <details style="background:#fff;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:12px;overflow:hidden">
                    <summary style="padding:18px 24px;font-weight:700;font-size:.95rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center">
                        Can I try it for free?
                        <span style="font-size:1.2rem;color:#ff9f43">+</span>
                    </summary>
                    <div style="padding:0 24px 18px;font-size:.9rem;color:#4a4a68;line-height:1.7">Yes. The Starter plan is completely free and supports a single branch with full POS, inventory, and customer management features.</div>
                </details>
            </div>
        </div>
    </section>

    <!-- PRICING -->
    <section class="demos-section" id="pricing" style="padding:80px 0">
        <div class="container">
            <div class="section-header">
                <div class="tag">Pricing</div>
                <h2>Simple, Transparent <span>Pricing</span></h2>
                <p>No hidden fees. No per-user charges. One price, full access.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:28px;max-width:1100px;margin:0 auto">
                @if($plans->isEmpty())
                    <div style="background:#fff;border:2px solid #f0f0f5;border-radius:16px;padding:36px;text-align:center">
                        <h3 style="font-size:1.1rem;font-weight:700;color:#8686a0;margin-bottom:8px">Starter</h3>
                        <div style="font-size:2.5rem;font-weight:800;color:#1a1a2e;margin-bottom:4px">Free</div>
                        <p style="font-size:.88rem;color:#8686a0;margin-bottom:24px">For single-store businesses</p>
                        <a href="/register" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">Get Started</a>
                    </div>
                @else
                    @foreach($plans as $index => $plan)
                        @php
                            $isPopular = $plan->is_default;
                            $features = [];
                            $features[] = $plan->max_users . ' Users';
                            $features[] = $plan->max_branches === 999 ? 'Unlimited Branches' : $plan->max_branches . ' Branch' . ($plan->max_branches > 1 ? 'es' : '');
                            $features[] = $plan->max_products === 999999 ? 'Unlimited Products' : number_format($plan->max_products) . ' Products';
                            if ($plan->multi_branch_enabled) $features[] = 'Stock Transfers';
                            if ($plan->online_store_enabled) $features[] = 'Online Storefront';
                            if ($plan->loyalty_enabled) $features[] = 'Loyalty Program';
                            if ($plan->wallet_enabled) $features[] = 'Digital Wallet';
                            if ($plan->advanced_reports_enabled) $features[] = 'Advanced Reports';
                            if ($plan->multi_unit_enabled) $features[] = 'Multi-Unit Products';
                            if ($plan->efris_enabled) $features[] = 'EFRIS Integration';
                            if ($plan->cloud_backup_enabled) $features[] = 'Cloud Backups';
                            if ($plan->api_access_enabled) $features[] = 'API Access';
                            $features[] = 'Email Support';
                            $monthlyPrice = $plan->price_monthly == 0 ? 'Free' : $plan->currency . ' ' . number_format($plan->price_monthly / 1000) . 'k';
                            $yearlyPrice = $plan->price_yearly > 0 ? '· ' . $plan->currency . ' ' . number_format($plan->price_yearly / 1000) . 'k/year' : '';
                        @endphp
                        @if($isPopular)
                            <div style="background:linear-gradient(135deg,#1a1a2e,#2d2d4a);border-radius:16px;padding:36px;text-align:center;color:#fff;position:relative;transform:scale(1.05);box-shadow:0 16px 40px rgba(26,26,46,.2)">
                                <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#ff9f43;color:#fff;padding:4px 16px;border-radius:20px;font-size:.75rem;font-weight:700">MOST POPULAR</div>
                                <h3 style="font-size:1.1rem;font-weight:700;color:#a0a0c0;margin-bottom:8px">{{ $plan->name }}</h3>
                                <div style="font-size:2.5rem;font-weight:800;color:#fff;margin-bottom:4px">{{ $monthlyPrice }}</div>
                                <p style="font-size:.88rem;color:#a0a0c0;margin-bottom:24px">/month {{ $yearlyPrice }}</p>
                                <ul style="list-style:none;text-align:left;margin-bottom:28px">
                                    @foreach($features as $feature)
                                        <li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);font-size:.9rem">✓ {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <a href="/register" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">Get Started</a>
                            </div>
                        @else
                            <div style="background:#fff;border:2px solid #f0f0f5;border-radius:16px;padding:36px;text-align:center;transition:transform .2s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                                <h3 style="font-size:1.1rem;font-weight:700;color:#8686a0;margin-bottom:8px">{{ $plan->name }}</h3>
                                <div style="font-size:2.5rem;font-weight:800;color:#1a1a2e;margin-bottom:4px">{{ $monthlyPrice }}</div>
                                <p style="font-size:.88rem;color:#8686a0;margin-bottom:24px">/month {{ $yearlyPrice }}</p>
                                <ul style="list-style:none;text-align:left;margin-bottom:28px">
                                    @foreach($features as $feature)
                                        <li style="padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:.9rem">✓ {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <a href="/register" class="btn btn-outline btn-lg" style="width:100%;justify-content:center">Get Started</a>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to <span>Transform</span> Your Business?</h2>
                <p>Join businesses already using HALIS to manage their sales, inventory, and multi-branch operations.</p>
                <div class="cta-btns">
                    <a href="/tenant" class="btn btn-primary btn-lg">🚀 Start Free Today</a>
                    <a href="#features" class="btn btn-white btn-lg">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="logo"><span class="dot"></span> HALIS</a>
                    <p>Modern POS & Inventory Management System built with Laravel, Filament, and Livewire for retail, restaurant, and multi-store businesses.</p>
                </div>
                <div>
                    <h4>Product</h4>
                    <a href="#features">Features</a>
                    <a href="#demos">Demos</a>
                    <a href="#modules">Modules</a>
                    <a href="#pricing">Pricing</a>
                </div>
                <div>
                    <h4>Business</h4>
                    <a href="/nova-retail-hub">Online Shop</a>
                    <a href="/tenant">Admin Panel</a>
                    <a href="#">API Docs</a>
                </div>
                <div>
                    <h4>Company</h4>
                    <a href="#">About</a>
                    <a href="#">Contact</a>
                    <a href="#">Support</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} HALIS. All rights reserved.</span>
                <div class="socials">
                    <a href="#">𝕏</a>
                    <a href="#">in</a>
                    <a href="#">▶</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('header').classList.toggle('scrolled', window.scrollY > 10);
        });

        // Smooth scroll for nav links
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const target = document.querySelector(a.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>
