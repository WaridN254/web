@extends('platform.layout')
@section('title', 'Dashboard')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Total Businesses</div>
        <div class="value">{{ $businessStats['total'] }}</div>
        <div class="sub">{{ $businessStats['new_this_month'] }} new this month</div>
    </div>
    <div class="stat-card">
        <div class="label">Active Businesses</div>
        <div class="value" style="color:#22c55e">{{ $businessStats['active'] }}</div>
        <div class="sub">{{ $businessStats['trial'] }} on trial</div>
    </div>
    <div class="stat-card">
        <div class="label">Active Subscriptions</div>
        <div class="value" style="color:#6366f1">{{ $subscriptionStats['active'] }}</div>
        <div class="sub">{{ $subscriptionStats['expiring_soon'] }} expiring soon</div>
    </div>
    <div class="stat-card">
        <div class="label">MRR</div>
        <div class="value" style="color:#f59e0b">UGX {{ number_format($revenueStats['mrr']) }}</div>
        <div class="sub">Revenue this month: UGX {{ number_format($revenueStats['revenue_this_month']) }}</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Suspended Businesses</div>
        <div class="value" style="color:#ef4444">{{ $businessStats['suspended'] }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Past Due</div>
        <div class="value" style="color:#f97316">{{ $subscriptionStats['past_due'] }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Total Users</div>
        <div class="value">{{ number_format($globalUsage['total_users']) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Total Transactions</div>
        <div class="value">{{ number_format($globalUsage['total_transactions']) }}</div>
    </div>
</div>

<!-- Email Health -->
<div class="stat-grid" style="margin-top:8px">
    <div class="stat-card" style="border-left:4px solid {{ config('mail.default') === 'log' ? '#f59e0b' : '#22c55e' }}">
        <div class="label">Email Status</div>
        <div class="value" style="font-size:1.1rem;color:{{ config('mail.default') === 'log' ? '#f59e0b' : '#22c55e' }}">{{ config('mail.default') === 'log' ? 'Dev Mode' : 'Live' }}</div>
        <div class="sub">Mailer: {{ ucfirst(config('mail.default')) }} | From: {{ config('email.from.address') }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Emails Sent (24h)</div>
        <div class="value" style="color:#6366f1">{{ \App\Models\EmailLog::where('created_at','>=',now()->subDay())->where('status','sent')->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Failed Emails (24h)</div>
        <div class="value" style="color:{{ \App\Models\EmailLog::where('created_at','>=',now()->subDay())->where('status','failed')->count() > 0 ? '#ef4444' : '#22c55e' }}">{{ \App\Models\EmailLog::where('created_at','>=',now()->subDay())->where('status','failed')->count() }}</div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="window.location='{{ route('platform.email-logs.index') }}'">
        <div class="label">Email Logs</div>
        <div class="value">{{ number_format(\App\Models\EmailLog::count()) }}</div>
        <div class="sub">View all →</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:8px">
    <div class="table-card">
        <div class="table-header">
            <h3>Recent Activity</h3>
            <a href="{{ route('platform.audit-logs.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead>
                <tr><th>Action</th><th>Description</th><th>Time</th></tr>
            </thead>
            <tbody>
                @forelse($recentActivity as $log)
                <tr>
                    <td><span class="badge badge-info">{{ $log->action }}</span></td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->description }}</td>
                    <td style="white-space:nowrap;color:#64748b;font-size:.82rem">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">No recent activity</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Quick Stats</h3>
        </div>
        <div style="padding:20px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px">
                    <div style="font-size:1.5rem;font-weight:800;color:#6366f1">{{ $globalUsage['total_branches'] }}</div>
                    <div style="font-size:.8rem;color:#64748b;font-weight:600">Total Branches</div>
                </div>
                <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px">
                    <div style="font-size:1.5rem;font-weight:800;color:#22c55e">{{ $globalUsage['total_products'] }}</div>
                    <div style="font-size:.8rem;color:#64748b;font-weight:600">Total Products</div>
                </div>
                <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px">
                    <div style="font-size:1.5rem;font-weight:800;color:#f59e0b">{{ $globalUsage['total_customers'] }}</div>
                    <div style="font-size:.8rem;color:#64748b;font-weight:600">Total Customers</div>
                </div>
                <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px">
                    <div style="font-size:1.5rem;font-weight:800;color:#ef4444">{{ $subscriptionStats['cancelled'] }}</div>
                    <div style="font-size:.8rem;color:#64748b;font-weight:600">Cancelled</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
