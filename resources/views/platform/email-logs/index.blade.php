@extends('platform.layout')

@section('title', 'Email Logs')

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div>
        <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:4px">Email Logs</h2>
        <p style="font-size:.85rem;color:#64748b">Monitor transactional email delivery and system health</p>
    </div>
    <div style="display:flex;gap:8px">
        <button onclick="openModal('test-email-modal')" class="btn btn-primary btn-sm">Send Test Email</button>
    </div>
</div>

<!-- Email Health Cards -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px">
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">Mailer</div>
        <div style="font-size:1.1rem;font-weight:700;color:{{ $mailer === 'log' ? '#f59e0b' : '#10b981' }}">{{ ucfirst($mailer) }}</div>
        <div style="font-size:.75rem;color:#94a3b8;margin-top:2px">{{ $mailer === 'log' ? 'Dev mode — emails logged' : 'Production — live sending' }}</div>
    </div>
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">Queue</div>
        <div style="font-size:1.1rem;font-weight:700">{{ ucfirst($queueConnection) }}</div>
        <div style="font-size:.75rem;color:#94a3b8;margin-top:2px">{{ $queueConnection === 'sync' ? 'Synchronous — no queue' . chr(10) . 'Running' : 'Async — requires worker' }}</div>
    </div>
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">From Address</div>
        <div style="font-size:1rem;font-weight:700">{{ $fromAddress }}</div>
    </div>
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">Sent Today</div>
        <div style="font-size:1.5rem;font-weight:800;color:#10b981">{{ $stats['sent_today'] }}</div>
    </div>
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">Queued Today</div>
        <div style="font-size:1.5rem;font-weight:800;color:#6366f1">{{ $stats['queued_today'] }}</div>
    </div>
    <div class="stat-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#64748b;margin-bottom:4px">Failed Today</div>
        <div style="font-size:1.5rem;font-weight:800;color:{{ $stats['failed_today'] > 0 ? '#ef4444' : '#10b981' }}">{{ $stats['failed_today'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px">
    <form method="GET" action="{{ route('platform.email-logs.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
        <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px">Recipient</label>
            <input type="text" name="recipient" value="{{ request('recipient') }}" placeholder="Search email..."
                   style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem;width:200px">
        </div>
        <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px">Type</label>
            <select name="type" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem;background:#fff">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px">Status</label>
            <select name="status" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem;background:#fff">
                <option value="">All Statuses</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px">Period</label>
            <select name="days" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem;background:#fff">
                <option value="">All Time</option>
                <option value="1" {{ request('days') === '1' ? 'selected' : '' }}>Today</option>
                <option value="7" {{ request('days') === '7' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ request('days') === '30' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <a href="{{ route('platform.email-logs.index') }}" class="btn btn-outline btn-sm">Clear</a>
    </form>
</div>

<!-- Email Logs Table -->
<div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Recipient</th>
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Type</th>
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Status</th>
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Tenant</th>
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Error</th>
                <th style="padding:12px 16px;text-align:left;font-size:.8rem;font-weight:600;color:#64748b">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:12px 16px;font-size:.85rem">{{ $log->recipient }}</td>
                    <td style="padding:12px 16px">
                        <span class="badge badge-info" style="font-size:.75rem">{{ str_replace('_', ' ', $log->type) }}</span>
                    </td>
                    <td style="padding:12px 16px">
                        @if($log->status === 'sent')
                            <span class="badge badge-success">Sent</span>
                        @elseif($log->status === 'queued')
                            <span class="badge badge-info">Queued</span>
                        @else
                            <span class="badge badge-danger">Failed</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:.85rem;color:#64748b">{{ $log->tenant?->name ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:.8rem;color:#ef4444;max-width:200px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->error ?? '—' }}</span>
                            @if($log->error)
                                <button onclick='showErrorDetails(@json($log->error))' style="background:none;border:none;cursor:pointer;color:#64748b;padding:0" title="View full error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:.85rem;color:#64748b">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:.9rem">No email logs found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($logs->hasPages())
        <div style="padding:16px;border-top:1px solid #e2e8f0">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- Test Email Modal -->
<div class="modal-overlay" id="test-email-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Send Test Email</h3>
            <button onclick="closeModal('test-email-modal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="{{ route('platform.email-logs.test') }}">
            @csrf
            <div class="modal-body">
                <p style="font-size:.85rem;color:#64748b;margin-bottom:16px">Send a test email to verify your transactional email configuration is working.</p>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:6px">Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com"
                       style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem"
                       value="{{ auth('platform')->user()->email ?? '' }}">
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('test-email-modal')" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Send Test</button>
            </div>
        </form>
    </div>
</div>

<!-- Error Details Modal -->
<div class="modal-overlay" id="error-details-modal">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3>Error Details</h3>
            <button onclick="closeModal('error-details-modal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer">&times;</button>
        </div>
        <div class="modal-body">
            <pre id="error-details-content" style="background:#f1f5f9;padding:12px;border-radius:8px;font-size:.8rem;color:#ef4444;white-space:pre-wrap;max-height:400px;overflow-y:auto;word-wrap:break-word;font-family:monospace"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('error-details-modal')" class="btn btn-outline btn-sm">Close</button>
        </div>
    </div>
</div>

<script>
    function showErrorDetails(errorText) {
        document.getElementById('error-details-content').textContent = errorText;
        openModal('error-details-modal');
    }
</script>
@endsection
