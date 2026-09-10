@php
    $stats = $this->getEfrisSubmissionStats();
    $taxCategories = \App\Models\TaxCategory::query()
        ->orderBy('code')
        ->get(['name', 'code', 'rate', 'is_active']);
    $apiLogs = \App\Models\EfrisApiLog::query()
        ->latest('created_at')
        ->limit(6)
        ->get(['endpoint', 'status', 'created_at']);
@endphp

<x-filament-panels::page>
    <style>
        .efris-page,
        .efris-page * {
            box-sizing: border-box;
        }

        .efris-page {
            --efris-navy: #102a43;
            --efris-orange: #ff9638;
            --efris-orange-dark: #f18422;
            --efris-border: #e3e8ed;
            --efris-muted: #6b7280;
            --efris-soft: #f5f7f9;
            margin: -8px auto 0;
            max-width: 1180px;
            color: #243447;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .efris-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px;
            border: 1px solid var(--efris-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
        }

        .efris-title-row {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .efris-icon-box {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            flex: 0 0 48px;
            border-radius: 12px;
            background: #fff0e2;
            color: var(--efris-orange-dark);
        }

        .efris-title {
            margin: 0;
            color: var(--efris-navy);
            font-size: 24px;
            font-weight: 850;
            letter-spacing: -.025em;
            line-height: 1.1;
        }

        .efris-subtitle {
            margin: 6px 0 0;
            color: var(--efris-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .efris-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 34px;
            padding: 0 13px;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            background: #ecfdf3;
            color: #047857;
            font-size: 12px;
            font-weight: 780;
            white-space: nowrap;
        }

        .efris-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
        }

        .efris-tabs {
            display: flex;
            gap: 6px;
            margin-top: 16px;
            padding: 6px;
            overflow-x: auto;
            border: 1px solid var(--efris-border);
            border-radius: 12px;
            background: #ffffff;
        }

        .efris-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 13px;
            font-weight: 760;
            white-space: nowrap;
            transition: background .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .efris-tab:hover {
            background: #f8fafc;
            color: var(--efris-navy);
        }

        .efris-tab.is-active {
            background: var(--efris-orange);
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(255, 150, 56, .2);
        }

        .efris-content {
            margin-top: 16px;
        }

        .efris-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .efris-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .efris-card {
            overflow: hidden;
            border: 1px solid var(--efris-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .045);
        }

        .efris-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 17px 18px;
            border-bottom: 1px solid var(--efris-border);
            background: #fbfcfd;
        }

        .efris-card-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            color: var(--efris-navy);
            font-size: 15px;
            font-weight: 830;
        }

        .efris-card-subtitle {
            margin: 4px 0 0;
            color: var(--efris-muted);
            font-size: 12px;
        }

        .efris-card-body {
            padding: 18px;
        }

        .efris-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .efris-field label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 780;
        }

        .efris-input {
            width: 100%;
            height: 40px;
            border: 1px solid var(--efris-border);
            border-radius: 9px;
            background: #ffffff;
            color: #243447;
            font-size: 13px;
            outline: 0;
            padding: 0 12px;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .efris-input::placeholder {
            color: #94a3b8;
        }

        .efris-input:focus {
            border-color: var(--efris-orange);
            box-shadow: 0 0 0 3px rgba(255, 150, 56, .13);
        }

        .efris-alert {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            padding: 13px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 13px;
            line-height: 1.5;
        }

        .efris-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .efris-btn {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 780;
            padding: 0 15px;
            transition: background .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .efris-btn:hover {
            transform: translateY(-1px);
        }

        .efris-btn.secondary {
            border: 1px solid var(--efris-border);
            background: #ffffff;
            color: var(--efris-navy);
        }

        .efris-btn.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .efris-btn.primary {
            border: 1px solid var(--efris-orange);
            background: var(--efris-orange);
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(255, 150, 56, .2);
        }

        .efris-btn.primary:hover {
            background: var(--efris-orange-dark);
            border-color: var(--efris-orange-dark);
        }

        .efris-stat {
            padding: 18px;
            border: 1px solid var(--efris-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .045);
        }

        .efris-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .efris-stat-label {
            color: var(--efris-muted);
            font-size: 12px;
            font-weight: 780;
        }

        .efris-stat-value {
            margin-top: 8px;
            color: var(--efris-navy);
            font-size: 30px;
            font-weight: 860;
            line-height: 1;
        }

        .efris-stat-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--efris-soft);
            color: #163957;
        }

        .efris-stat.success .efris-stat-icon { background: #dcfce7; color: #16a34a; }
        .efris-stat.danger .efris-stat-icon { background: #fee2e2; color: #dc2626; }
        .efris-stat.warning .efris-stat-icon { background: #fff7ed; color: var(--efris-orange-dark); }

        .efris-list {
            display: grid;
            gap: 10px;
        }

        .efris-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px;
            border: 1px solid var(--efris-border);
            border-radius: 12px;
            background: #ffffff;
        }

        .efris-row-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .efris-code {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 38px;
            border-radius: 10px;
            background: #fff0e2;
            color: var(--efris-orange-dark);
            font-size: 13px;
            font-weight: 860;
        }

        .efris-row-title {
            margin: 0;
            overflow: hidden;
            color: var(--efris-navy);
            font-size: 14px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .efris-row-meta {
            margin: 4px 0 0;
            color: var(--efris-muted);
            font-size: 12px;
        }

        .efris-rate {
            min-width: 76px;
            text-align: right;
        }

        .efris-rate strong {
            display: block;
            color: var(--efris-navy);
            font-size: 14px;
        }

        .efris-rate span {
            color: var(--efris-muted);
            font-size: 11px;
        }

        .efris-pill {
            display: inline-flex;
            align-items: center;
            height: 24px;
            border-radius: 999px;
            padding: 0 9px;
            font-size: 11px;
            font-weight: 800;
            text-transform: capitalize;
        }

        .efris-pill.success { background: #dcfce7; color: #166534; }
        .efris-pill.danger { background: #fee2e2; color: #991b1b; }
        .efris-pill.muted { background: #f1f5f9; color: #475569; }

        .efris-empty {
            display: grid;
            min-height: 190px;
            place-items: center;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            color: var(--efris-muted);
            text-align: center;
            padding: 24px;
        }

        .efris-empty strong {
            display: block;
            margin-top: 10px;
            color: #334155;
            font-size: 14px;
        }

        .efris-empty span {
            display: block;
            margin-top: 5px;
            font-size: 13px;
        }

        @media (max-width: 860px) {
            .efris-hero,
            .efris-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .efris-grid,
            .efris-form-grid,
            .efris-stats-grid {
                grid-template-columns: 1fr;
            }

            .efris-rate {
                text-align: left;
            }
        }
    </style>

    <div class="efris-page">
        <section class="efris-hero">
            <div class="efris-title-row">
                <span class="efris-icon-box" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 3v18l2-1 2 1 2-1 2 1 2-1 2 1 2-1V3Z" />
                        <path d="M8 7h8" />
                        <path d="M8 11h8" />
                        <path d="M8 15h5" />
                    </svg>
                </span>
                <div>
                    <h1 class="efris-title">Tax & EFRIS Management</h1>
                    <p class="efris-subtitle">Configure Uganda Revenue Authority fiscal receipt integration, tax categories, and submission monitoring.</p>
                </div>
            </div>
            <span class="efris-status"><span class="efris-dot"></span> System Active</span>
        </section>

        <nav class="efris-tabs" aria-label="Tax and EFRIS tabs">
            <button type="button" wire:click="$set('activeTab', 'settings')" class="efris-tab {{ $activeTab === 'settings' ? 'is-active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.05.05a2.15 2.15 0 1 1-3.04 3.04l-.05-.05a1.8 1.8 0 0 0-1.98-.36 1.8 1.8 0 0 0-1.1 1.65V21.4a2.15 2.15 0 1 1-4.3 0v-.08a1.8 1.8 0 0 0-1.1-1.65 1.8 1.8 0 0 0-1.98.36l-.05.05a2.15 2.15 0 1 1-3.04-3.04l.05-.05A1.8 1.8 0 0 0 4.6 15a1.8 1.8 0 0 0-1.65-1.1H2.85a2.15 2.15 0 1 1 0-4.3h.08A1.8 1.8 0 0 0 4.6 8.5a1.8 1.8 0 0 0-.36-1.98l-.05-.05a2.15 2.15 0 1 1 3.04-3.04l.05.05a1.8 1.8 0 0 0 1.98.36 1.8 1.8 0 0 0 1.1-1.65V2.1a2.15 2.15 0 1 1 4.3 0v.08a1.8 1.8 0 0 0 1.1 1.65 1.8 1.8 0 0 0 1.98-.36l.05-.05a2.15 2.15 0 1 1 3.04 3.04l-.05.05a1.8 1.8 0 0 0-.36 1.98 1.8 1.8 0 0 0 1.65 1.1h.08a2.15 2.15 0 1 1 0 4.3h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>
                Settings
            </button>
            <button type="button" wire:click="$set('activeTab', 'submissions')" class="efris-tab {{ $activeTab === 'submissions' ? 'is-active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                Submissions
            </button>
            <button type="button" wire:click="$set('activeTab', 'tax-categories')" class="efris-tab {{ $activeTab === 'tax-categories' ? 'is-active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7v7H4z"/><path d="M13 6h7v7h-7z"/><path d="M4 15h7v3H4z"/><path d="M13 15h7v3h-7z"/></svg>
                Tax Categories
            </button>
            <button type="button" wire:click="$set('activeTab', 'api-logs')" class="efris-tab {{ $activeTab === 'api-logs' ? 'is-active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                API Logs
            </button>
        </nav>

        <div class="efris-content">
            @if ($activeTab === 'settings')
                <div class="efris-card">
                    <div class="efris-card-header">
                        <div>
                            <h2 class="efris-card-title">URA EFRIS Integration</h2>
                            <p class="efris-card-subtitle">Fiscal receipt system credentials and endpoint configuration.</p>
                        </div>
                    </div>
                    <div class="efris-card-body">
                        <div class="efris-form-grid">
                            <div class="efris-field">
                                <label for="efris-tin">Business TIN</label>
                                <input id="efris-tin" type="text" class="efris-input" placeholder="Enter Business Tax Identification Number">
                            </div>
                            <div class="efris-field">
                                <label for="efris-client-id">Client ID</label>
                                <input id="efris-client-id" type="text" class="efris-input" placeholder="Enter Client ID">
                            </div>
                            <div class="efris-field">
                                <label for="efris-secret">Client Secret</label>
                                <input id="efris-secret" type="password" class="efris-input" placeholder="Enter Client Secret">
                            </div>
                            <div class="efris-field">
                                <label for="efris-url">API Base URL</label>
                                <input id="efris-url" type="url" class="efris-input" placeholder="https://api.efris.ura.go.ug">
                            </div>
                        </div>

                        <div class="efris-alert">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            <span>Confirm credentials with URA before saving. Use Test Connection to verify the EFRIS API endpoint.</span>
                        </div>

                        <div class="efris-actions">
                            <button type="button" wire:click="testEfrisConnection" class="efris-btn secondary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M16 8h5V3"/></svg>
                                Test Connection
                            </button>
                            <button type="button" wire:click="saveEfrisSettings" class="efris-btn primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                                Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            @elseif ($activeTab === 'submissions')
                <div class="efris-stats-grid">
                    <div class="efris-stat success">
                        <div class="efris-stat-top">
                            <div>
                                <div class="efris-stat-label">Submitted</div>
                                <div class="efris-stat-value">{{ number_format($stats['submitted']) }}</div>
                            </div>
                            <span class="efris-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg></span>
                        </div>
                    </div>
                    <div class="efris-stat danger">
                        <div class="efris-stat-top">
                            <div>
                                <div class="efris-stat-label">Failed</div>
                                <div class="efris-stat-value">{{ number_format($stats['failed']) }}</div>
                            </div>
                            <span class="efris-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></span>
                        </div>
                    </div>
                    <div class="efris-stat warning">
                        <div class="efris-stat-top">
                            <div>
                                <div class="efris-stat-label">Pending Retry</div>
                                <div class="efris-stat-value">{{ number_format($stats['failed']) }}</div>
                            </div>
                            <span class="efris-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg></span>
                        </div>
                    </div>
                </div>

                <div class="efris-actions">
                    <button type="button" wire:click="retryFailedSubmissions" class="efris-btn primary">Retry Failed Submissions</button>
                </div>
            @elseif ($activeTab === 'tax-categories')
                <div class="efris-card">
                    <div class="efris-card-header">
                        <div>
                            <h2 class="efris-card-title">Tax Categories</h2>
                            <p class="efris-card-subtitle">URA tax codes used while submitting fiscal receipts.</p>
                        </div>
                        <a href="/tenant/tax-categories/create" class="efris-btn primary">Add Category</a>
                    </div>
                    <div class="efris-card-body">
                        <div class="efris-list">
                            @forelse ($taxCategories as $category)
                                <div class="efris-row">
                                    <div class="efris-row-main">
                                        <span class="efris-code">{{ $category->code }}</span>
                                        <div>
                                            <p class="efris-row-title">{{ $category->name }}</p>
                                            <p class="efris-row-meta">{{ $category->is_active ? 'Active category' : 'Inactive category' }}</p>
                                        </div>
                                    </div>
                                    <div class="efris-rate">
                                        <strong>{{ number_format((float) $category->rate, 2) }}%</strong>
                                        <span>Tax Rate</span>
                                    </div>
                                </div>
                            @empty
                                <div class="efris-empty">
                                    <div>
                                        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7v7H4z"/><path d="M13 6h7v7h-7z"/><path d="M4 15h7v3H4z"/><path d="M13 15h7v3h-7z"/></svg>
                                        <strong>No tax categories yet</strong>
                                        <span>Create URA tax categories before processing EFRIS submissions.</span>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @elseif ($activeTab === 'api-logs')
                <div class="efris-card">
                    <div class="efris-card-header">
                        <div>
                            <h2 class="efris-card-title">API Logs</h2>
                            <p class="efris-card-subtitle">Recent requests sent to the EFRIS service.</p>
                        </div>
                    </div>
                    <div class="efris-card-body">
                        <div class="efris-list">
                            @forelse ($apiLogs as $log)
                                @php
                                    $statusClass = $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'muted');
                                @endphp
                                <div class="efris-row">
                                    <div>
                                        <p class="efris-row-title">{{ $log->endpoint ?: 'EFRIS Endpoint' }}</p>
                                        <p class="efris-row-meta">{{ $log->created_at?->format('M d, Y H:i') }}</p>
                                    </div>
                                    <span class="efris-pill {{ $statusClass }}">{{ $log->status ?: 'pending' }}</span>
                                </div>
                            @empty
                                <div class="efris-empty">
                                    <div>
                                        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                        <strong>No API logs yet</strong>
                                        <span>EFRIS API activity will appear here after fiscal submissions are processed.</span>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
