<x-filament-panels::page>
    @php
        $rpColumns = $this->getReportColumns();
        $rpAllRows = array_values($this->getReportRows());
        $rpPaged = $this->paginateRows($rpAllRows);
        $rpRows = $rpPaged['rows'];
        $rpTotals = $this->getTotalsRow();
        $rpStats = $this->getStatCards();
        $rpPills = $this->getNavPills();
        $rpFields = $this->getFilterFields();
        $rpTitle = $this->getTableTitle();
        $rpCurrency = $this->tenantCurrency();
        $rpDate = fn ($v) => $v === null || $v === '' ? '-' : (($v instanceof \Carbon\CarbonInterface) ? $v->format('M d, Y') : \Illuminate\Support\Carbon::parse($v)->format('M d, Y'));
        $rpQty = fn ($v) => number_format((float) $v, (float) $v == floor((float) $v) ? 0 : 2);
        $rpMoney = fn ($v) => number_format((float) ($v ?? 0), 2);
        $rpBadgeClass = fn ($col, $val) => ($col['colors'][$val] ?? $col['colors']['default'] ?? 'secondary');
    @endphp

    <style>
        .dreams-report-page {
            --rp-navy: #1e293b;
            --rp-ink: #334155;
            --rp-muted: #64748b;
            --rp-border: #e2e8f0;
            --rp-head-bg: #f1f5f9;
            --rp-white: #ffffff;
            --rp-orange: #f97316;
            --rp-green: #15803d;
            --rp-green-soft: #dcfce7;
            --rp-red: #b91c1c;
            --rp-red-soft: #fee2e2;
            --rp-amber: #b45309;
            --rp-amber-soft: #fef3c7;
            --rp-cyan: #0e7490;
            --rp-cyan-soft: #cffafe;
            --rp-purple: #7c3aed;
            --rp-purple-soft: #ede9fe;
            --rp-blue: #1d4ed8;
            --rp-blue-soft: #dbeafe;
            --rp-primary: #f97316;
            --rp-primary-soft: #ffedd5;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--rp-ink);
        }

        .dreams-report-page * { box-sizing: border-box; }

        .rp-hidden { display: none !important; }

        #rpCollapseHeader { transition: transform .2s ease; }

        #rpCollapseHeader.rp-rotated { transform: rotate(180deg); }

        .rp-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .rp-page-title h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--rp-navy);
        }

        .rp-page-title h6 {
            margin: 4px 0 0;
            font-size: 13px;
            font-weight: 400;
            color: var(--rp-muted);
        }

        ul.rp-top-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        ul.rp-top-head li button,
        ul.rp-top-head li a {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: var(--rp-white);
            border: 1px solid var(--rp-border);
            border-radius: 6px;
            color: var(--rp-muted);
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        ul.rp-top-head li button:hover,
        ul.rp-top-head li a:hover {
            background: var(--rp-head-bg);
            color: var(--rp-navy);
        }

        ul.rp-pills {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0 0 18px;
            padding: 6px;
            background: var(--rp-head-bg);
            border: 1px solid var(--rp-border);
            border-radius: 8px;
            list-style: none;
        }

        ul.rp-pills li a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--rp-muted);
            text-decoration: none;
            transition: all .15s ease;
            white-space: nowrap;
        }

        ul.rp-pills li a:hover { color: var(--rp-navy); }

        ul.rp-pills li a.rp-pill-active {
            background: var(--rp-primary);
            color: #fff;
        }

        .rp-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .rp-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--rp-white);
            border: 1px solid var(--rp-border);
            border-radius: 8px;
            padding: 16px 18px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }

        .rp-stat-icon {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: grid;
            place-items: center;
            border-radius: 8px;
        }

        .rp-stat-icon svg { width: 20px; height: 20px; }

        .rp-stat-icon.green { background: var(--rp-green-soft); color: var(--rp-green); }
        .rp-stat-icon.cyan { background: var(--rp-cyan-soft); color: var(--rp-cyan); }
        .rp-stat-icon.amber { background: var(--rp-amber-soft); color: var(--rp-amber); }
        .rp-stat-icon.red { background: var(--rp-red-soft); color: var(--rp-red); }
        .rp-stat-icon.blue { background: var(--rp-blue-soft); color: var(--rp-blue); }
        .rp-stat-icon.purple { background: var(--rp-purple-soft); color: var(--rp-purple); }

        .rp-stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--rp-muted);
            margin-bottom: 2px;
        }

        .rp-stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--rp-navy);
        }

        .rp-card {
            background: var(--rp-white);
            border: 1px solid var(--rp-border);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .rp-filter-card form.row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0;
            margin: 0;
        }

        .rp-filter-card .rp-col {
            flex: 1 1 200px;
            max-width: 100%;
            padding: 0 8px 12px;
            min-width: 0;
        }

        .rp-filter-card .rp-col.rp-col-btn { flex: 0 0 auto; }

        .rp-field-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--rp-muted);
            margin-bottom: 6px;
        }

        .rp-input {
            width: 100%;
            height: 38px;
            padding: 0 12px;
            border: 1px solid var(--rp-border);
            border-radius: 6px;
            background: var(--rp-white);
            font-size: 13px;
            color: var(--rp-ink);
            outline: 0;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .rp-input:focus {
            border-color: var(--rp-primary);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
        }

        select.rp-input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
            cursor: pointer;
        }

        .rp-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 38px;
            padding: 0 18px;
            border: 0;
            border-radius: 6px;
            background: var(--rp-primary);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: filter .15s ease;
            white-space: nowrap;
        }

        .rp-btn-primary:hover { filter: brightness(.95); }

        .rp-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--rp-border);
        }

        .rp-card-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--rp-navy);
        }

        ul.rp-export {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        ul.rp-export li button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: 1px solid var(--rp-border);
            border-radius: 6px;
            background: var(--rp-white);
            color: var(--rp-muted);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s ease;
        }

        ul.rp-export li button:hover {
            color: var(--rp-navy);
            border-color: #cbd5e1;
            background: var(--rp-head-bg);
        }

        ul.rp-export li button svg { width: 14px; height: 14px; }

        .rp-table-wrap { overflow-x: auto; }

        table.rp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 760px;
        }

        table.rp-table thead th {
            background: var(--rp-head-bg);
            color: var(--rp-navy);
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            padding: 12px 18px;
            border-bottom: 1px solid var(--rp-border);
            white-space: nowrap;
        }

        table.rp-table thead th.rp-right { text-align: right; }

        table.rp-table tbody td {
            padding: 13px 18px;
            border-bottom: 1px solid #eef1f4;
            color: var(--rp-muted);
            white-space: nowrap;
        }

        table.rp-table tbody tr:last-child td { border-bottom: 0; }

        table.rp-table tbody tr:hover td { background: #f8fafc; }

        table.rp-table tbody td.rp-right { text-align: right; }

        table.rp-table tfoot td {
            background: var(--rp-head-bg);
            color: var(--rp-navy);
            font-weight: 600;
            font-size: 13px;
            padding: 12px 18px;
            border-top: 1px solid var(--rp-border);
            white-space: nowrap;
        }

        table.rp-table tfoot td.rp-right { text-align: right; }

        .rp-money { color: var(--rp-ink); font-weight: 500; }

        .rp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .rp-badge .rp-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

        .rp-badge.green { background: var(--rp-green-soft); color: var(--rp-green); }
        .rp-badge.red { background: var(--rp-red-soft); color: var(--rp-red); }
        .rp-badge.amber { background: var(--rp-amber-soft); color: var(--rp-amber); }
        .rp-badge.cyan { background: var(--rp-cyan-soft); color: var(--rp-cyan); }
        .rp-badge.blue { background: var(--rp-blue-soft); color: var(--rp-blue); }
        .rp-badge.purple { background: var(--rp-purple-soft); color: var(--rp-purple); }
        .rp-badge.secondary { background: var(--rp-head-bg); color: var(--rp-muted); }

        .rp-empty {
            padding: 46px 20px;
            text-align: center;
            color: var(--rp-muted);
            font-size: 13px;
        }

        .rp-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 18px;
            border-top: 1px solid var(--rp-border);
            font-size: 12px;
            color: var(--rp-muted);
        }

        .rp-pagination .rp-pg-controls { display: flex; align-items: center; gap: 6px; }

        .rp-pagination button {
            min-width: 30px;
            height: 30px;
            padding: 0 10px;
            border: 1px solid var(--rp-border);
            border-radius: 6px;
            background: var(--rp-white);
            color: var(--rp-muted);
            font-size: 12px;
            cursor: pointer;
            transition: all .15s ease;
        }

        .rp-pagination button:hover:not(:disabled) { color: var(--rp-navy); border-color: #cbd5e1; }

        .rp-pagination button:disabled { opacity: .45; cursor: not-allowed; }

        .rp-pagination .rp-pg-page {
            display: inline-grid;
            place-items: center;
            min-width: 30px;
            height: 30px;
            border-radius: 6px;
            font-weight: 600;
            color: #fff;
            background: var(--rp-primary);
            padding: 0 8px;
        }

        @media (max-width: 640px) {
            .rp-filter-card .rp-col { flex: 1 1 100%; }
        }

        @media print {
            body * { visibility: hidden !important; }
            #rpPrintArea, #rpPrintArea * { visibility: visible !important; }
            #rpPrintArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                box-shadow: none;
            }
            .rp-pagination { display: none; }
            ul.rp-export { display: none; }
        }
    </style>

    <div class="dreams-report-page">
        <div class="rp-page-header">
            <div class="rp-page-title">
                <h4>{{ static::getTitle() }}</h4>
                <h6>{{ static::getSubtitle() }}</h6>
            </div>
            <ul class="rp-top-head">
                <li>
                    <a href="javascript:window.location.reload();" title="Refresh">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
                    </a>
                </li>
                @if(method_exists($this, 'openSendEmailModal'))
                    <li>
                        <button type="button" wire:click="openSendEmailModal('payment_reminder', '', '')" title="Send Payment Reminder" style="width:34px;height:34px;display:grid;place-items:center;background:var(--rp-white);border:1px solid var(--rp-border);border-radius:6px;color:var(--rp-muted);cursor:pointer;transition:background .15s ease, color .15s ease;">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="M19 8.839l-7.5 3.75a2.25 2.25 0 0 1-2 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                        </button>
                    </li>
                @endif
                @if (count($rpFields) > 0)
                    <li>
                        <button type="button" id="rpCollapseHeader" title="Collapse" onclick="document.querySelectorAll('.rp-collapsible').forEach(el => el.classList.toggle('rp-hidden')); this.classList.toggle('rp-rotated');">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        </button>
                    </li>
                @endif
            </ul>
        </div>

        @if (count($rpPills) > 0)
            <ul class="rp-pills">
                @foreach ($rpPills as $pill)
                    <li>
                        <a href="{{ $pill['url'] }}" class="{{ $this->isPillActive($pill['url']) ? 'rp-pill-active' : '' }}">
                            @if (isset($pill['icon']) && $pill['icon'])
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $pill['icon'] !!}</svg>
                            @endif
                            {{ $pill['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="rp-collapsible">
            @if (count($rpStats) > 0)
                <div class="rp-stats">
                    @foreach ($rpStats as $stat)
                        <div class="rp-stat">
                            <div class="rp-stat-icon {{ $stat['color'] }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $stat['icon'] !!}</svg>
                            </div>
                            <div>
                                <div class="rp-stat-label">{{ $stat['label'] }}</div>
                                <div class="rp-stat-value">{{ $stat['value'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if (count($rpFields) > 0)
            <div class="rp-card rp-filter-card rp-collapsible">
                <div class="rp-card-body" style="padding: 14px 10px 2px;">
                    <form class="row" wire:submit="generateReport">
                        @foreach ($rpFields as $field)
                            @if ($field['type'] === 'date')
                                <div class="rp-col">
                                    <label class="rp-field-label">{{ $field['label'] }}</label>
                                    <input type="date" class="rp-input" wire:model="filters.{{ $field['key'] }}">
                                </div>
                            @elseif ($field['type'] === 'select')
                                <div class="rp-col">
                                    <label class="rp-field-label">{{ $field['label'] }}</label>
                                    <select class="rp-input" wire:model="filters.{{ $field['key'] }}">
                                        @foreach ($field['options'] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                        <div class="rp-col rp-col-btn">
                            <button type="submit" class="rp-btn-primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 3v5h-5"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 21v-5h5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="rp-card rp-table-card" id="rpPrintArea">
            <div class="rp-card-header">
                <h4>{{ $rpTitle }}</h4>
                <ul class="rp-export">
                    <li>
                        <button type="button" title="PDF" onclick="window.print();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                            PDF
                        </button>
                    </li>
                    <li>
                        <button type="button" title="Excel" onclick="exportReportExcel('{{ addslashes($rpTitle) }}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                            Excel
                        </button>
                    </li>
                    <li>
                        <button type="button" title="Print" onclick="window.print();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                            Print
                        </button>
                    </li>
                </ul>
            </div>
            <div class="rp-table-wrap">
                <table class="rp-table" id="rpTable">
                    <thead>
                        <tr>
                            @foreach ($rpColumns as $col)
                                <th class="{{ ($col['align'] ?? 'left') === 'right' ? 'rp-right' : '' }}">{{ $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rpRows as $row)
                            <tr>
                                @foreach ($rpColumns as $col)
                                    @php
                                        $val = $row[$col['key']] ?? null;
                                        $type = $col['type'] ?? 'text';
                                    @endphp
                                    <td class="{{ ($col['align'] ?? 'left') === 'right' ? 'rp-right' : '' }}">
                                        @if ($type === 'money')
                                            <span class="rp-money">{{ $rpMoney($val) }}</span>
                                        @elseif ($type === 'number')
                                            {{ $rpQty($val) }}
                                        @elseif ($type === 'date')
                                            {{ $rpDate($val) }}
                                        @elseif ($type === 'badge')
                                            <span class="rp-badge {{ $rpBadgeClass($col, $val) }}">
                                                @if (($col['dot'] ?? true))
                                                    <span class="rp-dot"></span>
                                                @endif
                                                {{ $val }}
                                            </span>
                                        @elseif ($type === 'html')
                                            {!! $val ?? '-' !!}
                                        @else
                                            {{ $val ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($rpColumns) }}">
                                    <div class="rp-empty">{{ $this->getEmptyText() }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($rpRows) > 0 && $rpTotals !== null)
                        <tfoot>
                            <tr>
                                @foreach ($rpColumns as $col)
                                    <td class="{{ ($col['align'] ?? 'left') === 'right' ? 'rp-right' : '' }}">
                                        @if ($loop->first)
                                            {{ $rpTotals['label'] ?? 'Total' }}
                                        @elseif (isset($rpTotals['values'][$col['key']]))
                                            @if (($col['type'] ?? 'text') === 'money')
                                                {{ $rpMoney($rpTotals['values'][$col['key']]) }}
                                            @else
                                                {{ $rpQty($rpTotals['values'][$col['key']]) }}
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="rp-pagination">
                <span>Showing {{ $rpPaged['from'] }} to {{ $rpPaged['to'] }} of {{ $rpPaged['total'] }} entries</span>
                <div class="rp-pg-controls">
                    <button type="button" wire:click="prevPage" @disabled(!$rpPaged['hasPrev'])>&laquo; Prev</button>
                    <span class="rp-pg-page">{{ $rpPaged['page'] }}</span>
                    <button type="button" wire:click="nextPage" @disabled(!$rpPaged['hasNext'])>Next &raquo;</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportReportExcel(fileName) {
            var table = document.getElementById('rpTable');
            if (!table) return;
            var rows = table.querySelectorAll('tr');
            var csv = [];
            rows.forEach(function (tr) {
                var cols = tr.querySelectorAll('td, th');
                var line = [];
                cols.forEach(function (td) {
                    var text = td.innerText.replace(/"/g, '""');
                    line.push('"' + text + '"');
                });
                csv.push(line.join(','));
            });
            var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = (fileName || 'report') + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    @if(method_exists($this, 'openSendEmailModal'))
    @if($showSendEmailModal)
    <div style="position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" wire:click.self="closeSendEmailModal">
        <div style="background:#fff;border-radius:14px;width:92%;max-width:540px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">
                    @if($sendEmailDocType === 'statement') Send Account Statement
                    @elseif($sendEmailDocType === 'payment_reminder') Send Payment Reminder
                    @else Send Email
                    @endif
                </h3>
                <button type="button" wire:click="closeSendEmailModal" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;padding:2px 6px;border-radius:6px;">&times;</button>
            </div>
            <div style="flex:1;overflow-y:auto;padding:20px;">
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">To</label>
                    <input type="email" wire:model.live="sendEmailTo" placeholder="customer@example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Subject</label>
                    <input type="text" wire:model.live="sendEmailSubject" placeholder="Email subject..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:0;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Message (optional)</label>
                    <textarea wire:model.live="sendEmailMessage" rows="3" placeholder="Add a personal note..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
                </div>
            </div>
            @if($sendEmailResult)
            <div style="padding:12px 20px;font-size:13px;@if($sendEmailSuccess) background:#dcfce7;color:#166534;@else background:#fee2e2;color:#991b1b;@endif border-top:1px solid @if($sendEmailSuccess) #bbf7d0;@else #fecaca;@endif">
                {{ $sendEmailResult }}
            </div>
            @endif
            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" wire:click="closeSendEmailModal" style="padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#475569;">Cancel</button>
                <button type="button" wire:click="sendEmailFromModal" {{ $sendEmailSending ? 'disabled' : '' }} style="padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:{{ $sendEmailSending ? '#94a3b8' : '#2563eb' }};color:#fff;">
                    @if($sendEmailSending) Sending... @else Send Email @endif
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif
</x-filament-panels::page>