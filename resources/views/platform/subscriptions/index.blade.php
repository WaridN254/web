@extends('platform.layout')
@section('title', 'Subscriptions')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>All Subscriptions ({{ $subscriptions->total() }})</h3>
    </div>

    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0">
        <form method="GET" style="display:flex;gap:12px;align-items:center">
            <select class="form-select" name="status" style="max-width:160px">
                <option value="">All Status</option>
                <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                <option value="trialing" {{ request('status')==='trialing'?'selected':'' }}>Trialing</option>
                <option value="past_due" {{ request('status')==='past_due'?'selected':'' }}>Past Due</option>
                <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Business</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Price</th>
                <th>Cycle</th>
                <th>Ends At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $sub)
            <tr>
                <td style="font-weight:600">{{ $sub->tenant?->name ?? '—' }}</td>
                <td><span class="badge badge-info">{{ $sub->plan?->name ?? '—' }}</span></td>
                <td>
                    @if($sub->status==='active')
                        <span class="badge badge-success">Active</span>
                    @elseif($sub->status==='trialing')
                        <span class="badge badge-info">Trialing</span>
                    @elseif($sub->status==='past_due')
                        <span class="badge badge-warning">Past Due</span>
                    @elseif($sub->status==='cancelled')
                        <span class="badge badge-danger">Cancelled</span>
                    @else
                        <span class="badge badge-gray">{{ ucfirst($sub->status) }}</span>
                    @endif
                </td>
                <td>{{ $sub->currency }} {{ number_format($sub->price) }}</td>
                <td style="text-transform:capitalize">{{ $sub->billing_cycle }}</td>
                <td style="white-space:nowrap">{{ $sub->ends_at ? $sub->ends_at->format('d M Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:40px">No subscriptions found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $subscriptions->withQueryString()->links() }}
    </div>
</div>
@endsection
