@php
    $money = fn ($amount) => $currency . ' ' . number_format((float) $amount, 0);
    $number = fn ($amount) => number_format((float) $amount, 0);
    $delta = fn ($value) => (($value ?? 0) >= 0 ? '+' : '') . number_format((float) ($value ?? 0), 1) . '%';
    $periods = ['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'custom' => 'Custom'];
    $mainCards = ['netRevenue', 'refunds', 'grossRevenue', 'discounts'];
    $secondaryCards = ['profit', 'invoiceDue', 'expenses', 'customers'];
    $navItems = [
        ['label' => 'Sales Terminal', 'icon' => 'ST'],
        ['label' => 'Verify Returns', 'icon' => 'VR'],
        ['label' => 'Dashboard', 'icon' => 'DB', 'active' => true],
        ['label' => 'Categories', 'icon' => 'CA'],
        ['label' => 'Services', 'icon' => 'SV'],
        ['label' => 'Warranties', 'icon' => 'WA'],
        ['label' => 'Products', 'icon' => 'PR'],
        ['label' => 'Stock', 'icon' => 'SK'],
        ['label' => 'Purchase Orders', 'icon' => 'PO'],
        ['label' => 'Customers & Suppliers', 'icon' => 'CS'],
        ['label' => 'Reporting', 'icon' => 'RP'],
        ['label' => 'Notifications', 'icon' => 'NT'],
        ['label' => 'Users & Security', 'icon' => 'US'],
        ['label' => 'Tax & EFRIS', 'icon' => 'TX'],
        ['label' => 'Discounts', 'icon' => 'DS'],
        ['label' => 'Payment Methods', 'icon' => 'PM'],
        ['label' => 'Document Vault', 'icon' => 'DV'],
        ['label' => 'Compliance', 'icon' => 'CO'],
        ['label' => 'Settings', 'icon' => 'SE'],
        ['label' => 'Configurations', 'icon' => 'CF'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenant POS Dashboard</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #111512;
            --panel: #1d211c;
            --panel-2: #252418;
            --panel-3: #171a16;
            --line: rgba(255, 255, 255, 0.08);
            --muted: #a79d8e;
            --soft: #7c7366;
            --text: #fff8ec;
            --orange: #ff7a18;
            --orange-2: #f2a23a;
            --teal: #16a394;
            --blue: #0f4770;
            --indigo: #2f64e6;
            --green: #4ad160;
            --red: #ff4f45;
            --shadow: 0 20px 70px rgba(0, 0, 0, 0.32);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            display: grid;
            grid-template-columns: 224px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 16px 12px;
            background: #11100d;
            border-right: 1px solid var(--line);
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 8px 18px;
            min-height: 52px;
        }

        .brand-mark {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--orange), #ffc25b);
            color: #211105;
            font-weight: 900;
        }

        .brand-name {
            min-width: 0;
        }

        .brand-name strong,
        .user strong {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
        }

        .brand-name span,
        .user span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        .nav-group {
            display: grid;
            gap: 6px;
            padding: 6px 0 14px;
            border-bottom: 1px solid var(--line);
        }

        .nav-group + .nav-group {
            padding-top: 14px;
        }

        .nav-link {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            min-height: 38px;
            padding: 7px 10px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
        }

        .nav-link.active {
            background: var(--orange);
            color: #fff;
            box-shadow: 0 10px 30px rgba(255, 122, 24, 0.24);
        }

        .nav-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
        }

        .nav-icon {
            display: grid;
            width: 26px;
            height: 26px;
            place-items: center;
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.05);
            color: currentColor;
            font-size: 10px;
            font-weight: 800;
        }

        .active .nav-icon {
            background: rgba(255, 255, 255, 0.18);
        }

        .sidebar-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
            padding: 14px 8px 0;
        }

        .avatar {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 50%;
            background: var(--orange);
            color: #fff;
            font-weight: 800;
        }

        .main {
            min-width: 0;
            padding: 18px 20px 28px;
            background:
                radial-gradient(circle at top right, rgba(22, 163, 148, 0.08), transparent 34rem),
                linear-gradient(180deg, #151713, #101210);
        }

        .topbar {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: clamp(23px, 3vw, 30px);
            line-height: 1.08;
        }

        .subline {
            margin-top: 7px;
            color: var(--muted);
            font-size: 13px;
        }

        .subline strong {
            color: var(--orange);
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .segments,
        .date-pill {
            display: flex;
            align-items: center;
            min-height: 36px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.035);
            box-shadow: var(--shadow);
        }

        .segments a {
            display: grid;
            min-width: 54px;
            height: 32px;
            place-items: center;
            border-radius: 6px;
            color: var(--text);
            font-size: 12px;
            font-weight: 800;
        }

        .segments a.active {
            background: var(--orange);
        }

        .date-pill {
            gap: 8px;
            padding: 0 12px;
            color: var(--text);
            font-size: 13px;
            font-weight: 650;
        }

        .dashboard-grid {
            display: grid;
            gap: 12px;
            min-width: 0;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            min-width: 0;
            max-width: 100%;
        }

        .stat-card,
        .panel,
        .mini-card {
            border: 1px solid var(--line);
            border-radius: 7px;
            background: var(--panel);
            min-width: 0;
        }

        .stat-card {
            min-height: 92px;
            padding: 16px;
            overflow: hidden;
        }

        .stat-card.feature {
            position: relative;
            background: linear-gradient(135deg, var(--tone), color-mix(in oklab, var(--tone), #111 32%));
            border-color: color-mix(in oklab, var(--tone), white 20%);
        }

        .stat-card.feature:after {
            content: "";
            position: absolute;
            inset: auto -22px -34px auto;
            width: 120px;
            height: 120px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
        }

        .stat-label {
            color: rgba(255, 248, 236, 0.78);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .stat-value {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
            min-width: 0;
        }

        .delta {
            display: inline-grid;
            min-height: 21px;
            place-items: center;
            border-radius: 4px;
            padding: 0 7px;
            background: rgba(255, 255, 255, 0.16);
            color: #e8fff0;
            font-size: 11px;
            font-weight: 900;
        }

        .delta.dark {
            background: rgba(74, 209, 96, 0.12);
            color: var(--green);
        }

        .card-icon {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 11px;
            font-weight: 900;
        }

        .secondary .stat-card {
            min-height: 90px;
            background: var(--panel-2);
        }

        .secondary .stat-label {
            color: var(--muted);
        }

        .secondary .stat-value {
            font-size: 23px;
        }

        .secondary .card-icon {
            color: var(--orange);
            background: rgba(255, 122, 24, 0.13);
        }

        .panel {
            padding: 14px;
            background: #1d211a;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            font-size: 15px;
            font-weight: 900;
        }

        .panel-title .card-icon {
            color: var(--orange);
            background: rgba(255, 122, 24, 0.15);
        }

        .panel-link,
        .small-pill {
            color: var(--text);
            font-size: 12px;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .small-pill {
            display: inline-grid;
            min-height: 30px;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 10px;
            background: rgba(255, 255, 255, 0.03);
            text-decoration: none;
        }

        .analysis-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.85fr 0.9fr;
            gap: 12px;
            min-height: 230px;
        }

        .chart-empty,
        .empty {
            display: grid;
            min-height: 180px;
            place-items: center;
            color: var(--soft);
            font-size: 14px;
        }

        .legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .legend-chip,
        .mini-card {
            padding: 11px;
        }

        .legend-chip {
            min-width: 96px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.025);
        }

        .legend-chip span,
        .mini-card span,
        .table th {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .legend-chip strong,
        .mini-card strong {
            display: block;
            margin-top: 4px;
            font-size: 15px;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .movement-box {
            margin-top: 10px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.025);
        }

        .movement-box h3 {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .movement-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
            color: var(--muted);
            font-size: 13px;
        }

        .movement-line strong {
            color: var(--green);
        }

        .two-col {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 11px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.055);
            text-align: left;
            font-size: 13px;
        }

        .table th:last-child,
        .table td:last-child {
            text-align: right;
        }

        .status {
            display: inline-grid;
            min-height: 24px;
            place-items: center;
            border-radius: 999px;
            padding: 0 9px;
            background: rgba(74, 209, 96, 0.11);
            color: var(--green);
            font-size: 11px;
            font-weight: 800;
            text-transform: capitalize;
        }

        .list-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.055);
            font-size: 13px;
        }

        .list-row span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--muted);
        }

        .list-row strong {
            color: var(--text);
        }

        .tone-orange { --tone: var(--orange); }
        .tone-blue { --tone: var(--blue); }
        .tone-teal { --tone: var(--teal); }
        .tone-indigo { --tone: var(--indigo); }
        .accent-orange { color: var(--orange); }
        .accent-green { color: var(--green); }
        .accent-red { color: var(--red); }

        @media (max-width: 1120px) {
            .shell {
                grid-template-columns: 78px minmax(0, 1fr);
            }

            .brand-name,
            .nav-link span:last-child,
            .user,
            .logout-mark {
                display: none;
            }

            .nav-link {
                grid-template-columns: 1fr;
                justify-items: center;
                padding-inline: 8px;
            }

            .sidebar-footer {
                justify-content: center;
            }

            .stat-grid,
            .analysis-grid,
            .two-col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .shell {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                max-height: 230px;
            }

            .main {
                padding: 14px;
            }

            .topbar {
                display: grid;
            }

            .toolbar {
                justify-content: start;
            }

            .segments {
                overflow-x: auto;
                max-width: 100%;
            }

            .stat-grid,
            .analysis-grid,
            .two-col,
            .mini-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .panel-header {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .panel-header .segments {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">P</div>
            <div class="brand-name">
                <strong>{{ $tenant->name ?? 'POS Tenant' }}</strong>
                <span>{{ $tenant->plan ?? 'Tenant workspace' }}</span>
            </div>
        </div>

        <nav>
            <div class="nav-group">
                @foreach (array_slice($navItems, 0, 2) as $item)
                    <a class="nav-link" href="#">
                        <span class="nav-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="nav-group">
                @foreach (array_slice($navItems, 2, 8) as $item)
                    <a class="nav-link {{ ($item['active'] ?? false) ? 'active' : '' }}" href="#">
                        <span class="nav-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="nav-group">
                @foreach (array_slice($navItems, 10) as $item)
                    <a class="nav-link" href="#">
                        <span class="nav-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
            <div class="user">
                <strong>{{ $userName }}</strong>
                <span>Administrator</span>
            </div>
            <span class="logout-mark" style="margin-left:auto;color:var(--muted);font-weight:900;">Exit</span>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Welcome, {{ explode(' ', trim($userName))[0] ?: 'Admin' }}</h1>
                <div class="subline">You have <strong>{{ $number($stats['transactionsToday']['value']) }}</strong> sales transactions today</div>
            </div>
            <div class="toolbar">
                <div class="segments" aria-label="Dashboard period">
                    @foreach ($periods as $key => $label)
                        <a class="{{ $period === $key ? 'active' : '' }}" href="{{ route('tenant.dashboard', ['period' => $key]) }}">{{ $label }}</a>
                    @endforeach
                </div>
                <div class="date-pill">{{ $dateLabel }}</div>
            </div>
        </header>

        <section class="dashboard-grid">
            <div class="stat-grid">
                @foreach ($mainCards as $key)
                    @php($stat = $stats[$key])
                    <article class="stat-card feature tone-{{ $stat['tone'] }}">
                        <div class="stat-top">
                            <div>
                                <div class="stat-label">{{ $stat['label'] }}</div>
                                <div class="stat-value">
                                    <span>{{ $money($stat['value']) }}</span>
                                    <span class="delta">{{ $delta($stat['delta']) }}</span>
                                </div>
                            </div>
                            <div class="card-icon">{{ strtoupper(substr($stat['label'], 0, 2)) }}</div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="stat-grid secondary">
                @foreach ($secondaryCards as $key)
                    @php($stat = $stats[$key])
                    <article class="stat-card">
                        <div class="stat-top">
                            <div>
                                <div class="stat-value">{{ ($stat['isCount'] ?? false) ? $number($stat['value']) : $money($stat['value']) }}</div>
                                <div class="stat-label">{{ $stat['label'] }}</div>
                                <div style="margin-top:10px;color:var(--muted);font-size:12px;">
                                    <span class="delta dark">{{ $delta($stat['delta']) }}</span>
                                    <span>vs last period</span>
                                </div>
                            </div>
                            <div class="card-icon">{{ strtoupper(substr($stat['label'], 0, 2)) }}</div>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <span class="card-icon">RA</span>
                        <span>Revenue Analysis</span>
                    </div>
                    <div class="segments" aria-label="Revenue period">
                        @foreach ($periods as $key => $label)
                            <a class="{{ $period === $key ? 'active' : '' }}" href="{{ route('tenant.dashboard', ['period' => $key]) }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="analysis-grid">
                    <div>
                        <div class="legend-row">
                            <div class="legend-chip">
                                <span>Net Revenue</span>
                                <strong class="accent-orange">{{ $money($stats['netRevenue']['value']) }}</strong>
                            </div>
                            <div class="legend-chip">
                                <span>Gross Revenue</span>
                                <strong class="accent-green">{{ $money($stats['grossRevenue']['value']) }}</strong>
                            </div>
                        </div>
                        <div class="chart-empty">No revenue data available for this period</div>
                    </div>

                    <div>
                        <div class="mini-grid">
                            <div class="mini-card">
                                <span>Net</span>
                                <strong class="accent-orange">{{ $money($stats['netRevenue']['value']) }}</strong>
                            </div>
                            <div class="mini-card">
                                <span>Gross</span>
                                <strong class="accent-green">{{ $money($stats['grossRevenue']['value']) }}</strong>
                            </div>
                            <div class="mini-card">
                                <span>Discounts</span>
                                <strong class="accent-orange">{{ $money($stats['discounts']['value']) }}</strong>
                            </div>
                            <div class="mini-card">
                                <span>Refunds</span>
                                <strong class="accent-red">{{ $money($stats['refunds']['value']) }}</strong>
                            </div>
                        </div>
                        <div class="movement-box">
                            <h3>Period Movement</h3>
                            @foreach ($movement as $row)
                                <div class="movement-line">
                                    <span>{{ $row['label'] }}</span>
                                    <strong>{{ $money($row['value']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="chart-empty">No revenue data available for this period</div>
                </div>
            </section>

            <div class="two-col">
                <section class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <span class="card-icon">SS</span>
                            <span>Sales Statistics</span>
                        </div>
                        <span class="small-pill">{{ $periods[$period] ?? 'Month' }}</span>
                    </div>
                    <div class="legend-row">
                        <div class="legend-chip">
                            <span>Revenue</span>
                            <strong>{{ $money($stats['netRevenue']['value']) }}</strong>
                        </div>
                        <div class="legend-chip">
                            <span>Expense</span>
                            <strong>{{ $money($stats['expenses']['value']) }}</strong>
                        </div>
                    </div>
                    <div class="chart-empty">No revenue statistics</div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <span class="card-icon">RT</span>
                            <span>Recent Transactions</span>
                        </div>
                        <a class="panel-link" href="#">View All</a>
                    </div>
                    @if ($recentTransactions->isEmpty())
                        <div class="empty">No recent transactions</div>
                    @else
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('M d') }}</td>
                                    <td>{{ $transaction->customer_name ?: 'Walk-in Customer' }}</td>
                                    <td><span class="status">{{ $transaction->status }}</span></td>
                                    <td>{{ $money($transaction->total_amount) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            </div>

            <div class="two-col">
                <section class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <span class="card-icon">TC</span>
                            <span>Top Customers</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="small-pill">{{ $periods[$period] ?? 'Month' }}</span>
                            <a class="panel-link" href="#">View All</a>
                        </div>
                    </div>
                    @forelse ($topCustomers as $customer)
                        <div class="list-row">
                            <span>{{ $customer->name }}</span>
                            <strong>{{ $money($customer->total) }}</strong>
                        </div>
                    @empty
                        <div class="empty">No customer sales yet</div>
                    @endforelse
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <span class="card-icon">TG</span>
                            <span>Top Categories</span>
                        </div>
                        <span class="small-pill">{{ $periods[$period] ?? 'Month' }}</span>
                    </div>
                    @forelse ($topCategories as $category)
                        <div class="list-row">
                            <span>{{ $category->name }}</span>
                            <strong>{{ $money($category->total) }}</strong>
                        </div>
                    @empty
                        <div class="empty">No category sales yet</div>
                    @endforelse
                </section>
            </div>
        </section>
    </main>
</div>
</body>
</html>
