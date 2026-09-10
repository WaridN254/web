@extends('platform.layout')
@section('title', 'Businesses')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>All Businesses ({{ $businesses->total() }})</h3>
        <button class="btn btn-primary" onclick="openModal('createBusinessModal')">+ Add Business</button>
    </div>

    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input class="form-input" type="text" name="search" placeholder="Search businesses..." value="{{ request('search') }}" style="max-width:280px">
            <select class="form-select" name="status" style="max-width:160px">
                <option value="">All Status</option>
                <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                <option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option>
                <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            @if(request('search')||request('status'))
                <a href="{{ route('platform.businesses.index') }}" class="btn btn-outline btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Business</th>
                <th>Owner</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Branches</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($businesses as $tenant)
            <tr>
                <td>
                    <div style="font-weight:600">{{ $tenant->name }}</div>
                    <div style="font-size:.78rem;color:#64748b">{{ $tenant->slug }}</div>
                </td>
                <td>{{ $tenant->business?->owner_name ?? '—' }}</td>
                <td><span class="badge badge-info">{{ $tenant->subscription?->plan?->name ?? 'No Plan' }}</span></td>
                <td>
                    @if($tenant->status==='active')
                        <span class="badge badge-success">Active</span>
                    @elseif($tenant->status==='suspended')
                        <span class="badge badge-danger">Suspended</span>
                    @else
                        <span class="badge badge-gray">{{ ucfirst($tenant->status) }}</span>
                    @endif
                </td>
                <td>{{ $tenant->branches()->count() }}</td>
                <td style="white-space:nowrap">{{ $tenant->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('platform.businesses.show', $tenant->id) }}" class="btn btn-outline btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:40px">No businesses found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $businesses->withQueryString()->links() }}
    </div>
</div>

<!-- Create Business Modal -->
<div class="modal-overlay" id="createBusinessModal">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>Create New Business</h3>
            <button onclick="closeModal('createBusinessModal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" action="{{ route('platform.businesses.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Business Name *</label>
                        <input class="form-input" type="text" name="business_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Owner Name *</label>
                        <input class="form-input" type="text" name="owner_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Owner Email *</label>
                        <input class="form-input" type="email" name="owner_email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Owner Phone</label>
                        <input class="form-input" type="text" name="owner_phone">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input class="form-input" type="text" name="country" value="UG" maxlength="2">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Currency</label>
                        <input class="form-input" type="text" name="currency" value="UGX" maxlength="3">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <input class="form-input" type="text" name="timezone" value="Africa/Kampala">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trial Days</label>
                        <input class="form-input" type="number" name="trial_days" value="14" min="0" max="365">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Branch Name</label>
                    <input class="form-input" type="text" name="initial_branch_name" value="Main Branch">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('createBusinessModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Business</button>
            </div>
        </form>
    </div>
</div>
@endsection
