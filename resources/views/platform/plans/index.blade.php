@extends('platform.layout')
@section('title', 'Plans')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>Plans ({{ $plans->count() }})</h3>
        <button class="btn btn-primary" onclick="openModal('createPlanModal')">+ Add Plan</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Plan</th>
                <th>Price (Monthly)</th>
                <th>Price (Yearly)</th>
                <th>Users</th>
                <th>Branches</th>
                <th>Products</th>
                <th>Subscriptions</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td>
                    <div style="font-weight:700">{{ $plan->name }}</div>
                    <div style="font-size:.78rem;color:#64748b">{{ $plan->slug }}</div>
                </td>
                <td>{{ $plan->currency }} {{ number_format($plan->price_monthly) }}</td>
                <td>{{ $plan->currency }} {{ number_format($plan->price_yearly) }}</td>
                <td>{{ $plan->max_users }}</td>
                <td>{{ $plan->max_branches }}</td>
                <td>{{ number_format($plan->max_products) }}</td>
                <td>{{ $plan->subscriptions_count }}</td>
                <td>
                    @if($plan->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-gray">Inactive</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:40px">No plans created yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Create Plan Modal -->
<div class="modal-overlay" id="createPlanModal">
    <div class="modal" style="max-width:700px">
        <div class="modal-header">
            <h3>Create New Plan</h3>
            <button onclick="closeModal('createPlanModal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" action="{{ route('platform.plans.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plan Name *</label>
                        <input class="form-input" type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug *</label>
                        <input class="form-input" type="text" name="slug" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-input" name="description" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Price (UGX) *</label>
                        <input class="form-input" type="number" name="price_monthly" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Yearly Price (UGX) *</label>
                        <input class="form-input" type="number" name="price_yearly" required min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Max Users *</label>
                        <input class="form-input" type="number" name="max_users" value="5" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Branches *</label>
                        <input class="form-input" type="number" name="max_branches" value="1" required min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Max Products *</label>
                        <input class="form-input" type="number" name="max_products" value="100" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trial Days</label>
                        <input class="form-input" type="number" name="trial_days" value="14" min="0">
                    </div>
                </div>
                <div style="margin-top:12px">
                    <label class="form-label" style="margin-bottom:10px">Features</label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
                        <label class="form-check"><input type="checkbox" name="online_store_enabled" value="1"> Online Store</label>
                        <label class="form-check"><input type="checkbox" name="custom_domain_enabled" value="1"> Custom Domain</label>
                        <label class="form-check"><input type="checkbox" name="advanced_reports_enabled" value="1"> Advanced Reports</label>
                        <label class="form-check"><input type="checkbox" name="cloud_backup_enabled" value="1"> Cloud Backup</label>
                        <label class="form-check"><input type="checkbox" name="api_access_enabled" value="1"> API Access</label>
                        <label class="form-check"><input type="checkbox" name="multi_unit_enabled" value="1"> Multi Unit</label>
                        <label class="form-check"><input type="checkbox" name="efris_enabled" value="1"> EFRIS</label>
                        <label class="form-check"><input type="checkbox" name="loyalty_enabled" value="1"> Loyalty</label>
                        <label class="form-check"><input type="checkbox" name="wallet_enabled" value="1"> Wallet</label>
                        <label class="form-check"><input type="checkbox" name="multi_branch_enabled" value="1"> Multi Branch</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('createPlanModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
