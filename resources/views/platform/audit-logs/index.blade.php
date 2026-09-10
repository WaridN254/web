@extends('platform.layout')
@section('title', 'Audit Logs')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>Audit Logs ({{ $logs->total() }})</h3>
    </div>

    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input class="form-input" type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="max-width:240px">
            <select class="form-select" name="action" style="max-width:180px">
                <option value="">All Actions</option>
                <option value="business_created" {{ request('action')==='business_created'?'selected':'' }}>Business Created</option>
                <option value="business_suspended" {{ request('action')==='business_suspended'?'selected':'' }}>Business Suspended</option>
                <option value="business_activated" {{ request('action')==='business_activated'?'selected':'' }}>Business Activated</option>
                <option value="subscription_created" {{ request('action')==='subscription_created'?'selected':'' }}>Subscription Created</option>
                <option value="plan_changed" {{ request('action')==='plan_changed'?'selected':'' }}>Plan Changed</option>
                <option value="platform_login" {{ request('action')==='platform_login'?'selected':'' }}>Platform Login</option>
                <option value="platform_user_created" {{ request('action')==='platform_user_created'?'selected':'' }}>User Created</option>
                <option value="business_impersonated" {{ request('action')==='business_impersonated'?'selected':'' }}>Impersonation</option>
            </select>
            <input class="form-input" type="date" name="date_from" value="{{ request('date_from') }}" style="max-width:160px">
            <input class="form-input" type="date" name="date_to" value="{{ request('date_to') }}" style="max-width:160px">
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            @if(request()->hasAny(['search','action','date_from','date_to']))
                <a href="{{ route('platform.audit-logs.index') }}" class="btn btn-outline btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Action</th>
                <th>User</th>
                <th>Business</th>
                <th>Description</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="white-space:nowrap;font-size:.82rem;color:#64748b">{{ $log->created_at->format('d M H:i') }}</td>
                <td><span class="badge badge-info">{{ $log->action }}</span></td>
                <td>{{ $log->platformUser?->name ?? '—' }}</td>
                <td>{{ $log->tenant?->name ?? '—' }}</td>
                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->description }}</td>
                <td style="font-size:.82rem;color:#64748b">{{ $log->ip_address }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:40px">No audit logs found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
