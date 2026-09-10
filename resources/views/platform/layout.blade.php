<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Platform Admin') - HALIS SaaS</title>
    @include('partials.pwa')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1a1a2e; background: #f1f5f9; line-height: 1.5; }
        a { text-decoration: none; color: inherit; }

        /* Layout */
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: #fff; position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar-header h2 { font-size: 1.1rem; font-weight: 700; }
        .sidebar-header .badge { display: inline-block; background: #6366f1; color: #fff; font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; margin-left: 6px; }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .sidebar-section { padding: 0 16px; margin-bottom: 8px; }
        .sidebar-section-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; padding: 8px 0 4px; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-radius: 8px; font-size: .88rem; font-weight: 500; color: #94a3b8; transition: all .15s; }
        .sidebar-link:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-link.active { background: #6366f1; color: #fff; }
        .sidebar-link .icon { width: 18px; text-align: center; font-size: .9rem; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
        .sidebar-footer a { display: flex; align-items: center; gap: 8px; font-size: .85rem; color: #94a3b8; }
        .sidebar-footer a:hover { color: #fff; }

        .main { flex: 1; margin-left: 260px; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 0 32px; height: 64px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar h1 { font-size: 1.15rem; font-weight: 700; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-right .user-name { font-size: .85rem; font-weight: 600; }
        .content { padding: 32px; max-width: 1400px; }

        /* Cards */
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
        .stat-card .label { font-size: .8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
        .stat-card .value { font-size: 1.8rem; font-weight: 800; color: #1a1a2e; margin-top: 4px; }
        .stat-card .sub { font-size: .78rem; color: #94a3b8; margin-top: 2px; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }

        /* Table */
        .table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .table-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .table-header h3 { font-size: .95rem; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 16px; font-size: .78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        td { padding: 12px 16px; font-size: .88rem; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0e7ff; color: #3730a3; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: .85rem; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
        .btn-primary { background: #6366f1; color: #fff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #22c55e; color: #fff; }
        .btn-success:hover { background: #16a34a; }
        .btn-outline { background: transparent; border: 1px solid #e2e8f0; color: #475569; }
        .btn-outline:hover { border-color: #6366f1; color: #6366f1; }
        .btn-sm { padding: 5px 10px; font-size: .78rem; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: .88rem; font-family: inherit; transition: border-color .15s; }
        .form-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .form-select { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: .88rem; font-family: inherit; background: #fff; }
        .form-check { display: flex; align-items: center; gap: 8px; }
        .form-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: #6366f1; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-error { color: #dc2626; font-size: .8rem; margin-top: 4px; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: .88rem; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: #fff; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.05rem; font-weight: 700; }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; padding: 16px 20px; justify-content: center; }
        .pagination a, .pagination span { padding: 6px 12px; border-radius: 6px; font-size: .82rem; font-weight: 500; }
        .pagination a { color: #6366f1; }
        .pagination a:hover { background: #e0e7ff; }
        .pagination .active { background: #6366f1; color: #fff; }
        .pagination .disabled { color: #94a3b8; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>HALIS <span class="badge">PLATFORM</span></h2>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Overview</div>
                    <a href="{{ route('platform.dashboard') }}" class="sidebar-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
                        <span class="icon">📊</span> Dashboard
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Management</div>
                    <a href="{{ route('platform.businesses.index') }}" class="sidebar-link {{ request()->routeIs('platform.businesses.*') ? 'active' : '' }}">
                        <span class="icon">🏢</span> Businesses
                    </a>
                    <a href="{{ route('platform.subscriptions.index') }}" class="sidebar-link {{ request()->routeIs('platform.subscriptions.*') ? 'active' : '' }}">
                        <span class="icon">💳</span> Subscriptions
                    </a>
                    <a href="{{ route('platform.plans.index') }}" class="sidebar-link {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}">
                        <span class="icon">📋</span> Plans
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Administration</div>
                    <a href="{{ route('platform.platform-users.index') }}" class="sidebar-link {{ request()->routeIs('platform.platform-users.*') ? 'active' : '' }}">
                        <span class="icon">👥</span> Platform Users
                    </a>
                    <a href="{{ route('platform.audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('platform.audit-logs.*') ? 'active' : '' }}">
                        <span class="icon">📝</span> Audit Logs
                    </a>
                    <a href="{{ route('platform.email-logs.index') }}" class="sidebar-link {{ request()->routeIs('platform.email-logs.*') ? 'active' : '' }}">
                        <span class="icon">📧</span> Email Logs
                    </a>
                    <a href="{{ route('platform.settings.index') }}" class="sidebar-link {{ request()->routeIs('platform.settings.*') ? 'active' : '' }}">
                        <span class="icon">⚙️</span> Settings
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <a href="{{ route('platform.logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <span>🚪</span> Sign Out
                </a>
                <form id="logout-form" action="{{ route('platform.logout') }}" method="POST" style="display:none">@csrf</form>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <h1>@yield('title', 'Dashboard')</h1>
                <div class="topbar-right">
                    <span class="user-name">{{ auth('platform')->user()->name }}</span>
                </div>
            </header>
            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    </script>
    @stack('scripts')
</body>
</html>
