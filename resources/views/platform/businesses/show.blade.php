@extends('platform.layout')
@section('title', $business->name)

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div>
        <h2 style="font-size:1.3rem;font-weight:800">{{ $business->name }}</h2>
        <p style="font-size:.88rem;color:#64748b">{{ $business->slug }} · Created {{ $business->created_at->format('d M Y') }}</p>
    </div>
    <div style="display:flex;gap:8px">
        @if($business->status==='active')
            <form method="POST" action="{{ route('platform.businesses.suspend', $business->id) }}" onsubmit="return confirm('Suspend this business? This will prevent business users from accessing the POS.')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Suspend</button>
            </form>
        @else
            <form method="POST" action="{{ route('platform.businesses.activate', $business->id) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Activate</button>
            </form>
        @endif
        <form method="POST" action="{{ route('platform.businesses.destroy', $business->id) }}" id="deleteBusinessForm">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('deleteModal').style.display='flex'" style="display:inline-flex;align-items:center;gap:5px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Delete
            </button>
        </form>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Status</div>
        <div class="value" style="font-size:1.2rem">
            @if($business->status==='active')
                <span class="badge badge-success">Active</span>
            @elseif($business->status==='suspended')
                <span class="badge badge-danger">Suspended</span>
            @else
                <span class="badge badge-gray">{{ ucfirst($business->status) }}</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="label">Current Plan</div>
        <div class="value" style="font-size:1.2rem">{{ $business->subscription?->plan?->name ?? 'No Plan' }}</div>
        <div class="sub">Subscription: {{ ucfirst($business->subscription?->status ?? 'none') }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Branches</div>
        <div class="value">{{ $business->branches->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Users</div>
        <div class="value">{{ $business->users->count() }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <!-- Business Info -->
    <div class="table-card">
        <div class="table-header"><h3>Business Details</h3></div>
        <div style="padding:20px">
            <table style="width:100%">
                <tr><td style="font-weight:600;width:140px;color:#64748b">Name</td><td>{{ $business->name }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Slug</td><td>{{ $business->slug }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Status</td><td>{{ ucfirst($business->status) }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Currency</td><td>{{ $business->default_currency ?? 'UGX' }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Timezone</td><td>{{ $business->timezone ?? 'Africa/Kampala' }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Created</td><td>{{ $business->created_at->format('d M Y H:i') }}</td></tr>
                <tr><td style="font-weight:600;color:#64748b">Trial Ends</td><td>{{ $business->trial_ends_at ? $business->trial_ends_at->format('d M Y') : 'N/A' }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Usage -->
    <div class="table-card">
        <div class="table-header"><h3>Usage</h3></div>
        <div style="padding:20px">
            @foreach($usage as $key => $data)
            <div style="margin-bottom:12px">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:4px">
                    <span style="font-weight:600;text-transform:capitalize">{{ $key }}</span>
                    <span style="color:#64748b">{{ $data['current'] }}{{ $data['limit'] ? ' / '.$data['limit'] : '' }}</span>
                </div>
                @if($data['limit'])
                <div style="background:#e2e8f0;border-radius:4px;height:6px;overflow:hidden">
                    <div style="background:{{ $data['percentage'] > 80 ? '#ef4444' : '#6366f1' }};height:100%;width:{{ min($data['percentage'], 100) }}%;border-radius:4px"></div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Branches -->
<div class="table-card" style="margin-top:24px">
    <div class="table-header"><h3>Branches ({{ $business->branches->count() }})</h3></div>
    <table>
        <thead><tr><th>Branch</th><th>Code</th><th>Status</th><th>Default</th></tr></thead>
        <tbody>
            @forelse($business->branches as $branch)
            <tr>
                <td style="font-weight:600">{{ $branch->name }}</td>
                <td style="color:#64748b">{{ $branch->code }}</td>
                <td>
                    @if($branch->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-gray">Inactive</span>
                    @endif
                </td>
                <td>{{ $branch->is_default ? '✓' : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">No branches</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Users -->
<div class="table-card" style="margin-top:24px">
    <div class="table-header"><h3>Users ({{ $business->users->count() }})</h3></div>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($business->users as $user)
            <tr>
                <td style="font-weight:600">{{ $user->full_name ?? $user->name }}</td>
                <td style="color:#64748b">{{ $user->email }}</td>
                <td><span class="badge badge-info">{{ $user->role?->name ?? '—' }}</span></td>
                <td>
                    @if($user->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-gray">Inactive</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">No users</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Activity -->
<div class="table-card" style="margin-top:24px">
    <div class="table-header"><h3>Activity Log</h3></div>
    <table>
        <thead><tr><th>Action</th><th>Description</th><th>Time</th></tr></thead>
        <tbody>
            @forelse($activity as $log)
            <tr>
                <td><span class="badge badge-info">{{ $log->action }}</span></td>
                <td>{{ $log->description }}</td>
                <td style="white-space:nowrap;color:#64748b;font-size:.82rem">{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px">No activity recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden">
        <div style="display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid #e2e8f0">
            <div style="width:40px;height:40px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            </div>
            <h4 style="margin:0;font-size:16px;font-weight:650;color:#991b1b">Delete "{{ $business->name }}"?</h4>
        </div>
        <div style="padding:20px 24px">
            <p style="margin:0 0 12px;font-size:13px;color:#64748b;line-height:1.5">This will permanently delete this business and <strong>all</strong> of the following:</p>
            <ul style="margin:8px 0 0;padding-left:18px;font-size:13px;color:#b91c1c;line-height:1.7">
                <li>All products and inventory</li>
                <li>All sales and purchase records</li>
                <li>All branches and stock transfers</li>
                <li>All customers and suppliers</li>
                <li>All users and settings</li>
            </ul>
            <p style="margin:14px 0 0;font-size:13px;color:#0f172a"><strong>This action cannot be undone.</strong></p>
        </div>
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #e2e8f0;background:#fafafa">
            <button type="button" onclick="document.getElementById('deleteModal').style.display='none'" style="padding:8px 16px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;font-size:13px;font-weight:500;cursor:pointer;color:#334155">Cancel</button>
            <button type="submit" form="deleteBusinessForm" style="padding:8px 16px;border:0;border-radius:7px;background:#dc2626;color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Yes, Delete Everything
            </button>
        </div>
    </div>
</div>
