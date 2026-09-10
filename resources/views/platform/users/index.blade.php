@extends('platform.layout')
@section('title', 'Platform Users')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>Platform Users ({{ $users->total() }})</h3>
        <button class="btn btn-primary" onclick="openModal('createUserModal')">+ Add User</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Permissions</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="font-weight:600">{{ $user->name }}</td>
                <td style="color:#64748b">{{ $user->email }}</td>
                <td>
                    @if($user->is_super_admin)
                        <span class="badge badge-warning">Super Admin</span>
                    @else
                        @foreach($user->roles as $role)
                            <span class="badge badge-info">{{ $role->name }}</span>
                        @endforeach
                        @if($user->roles->isEmpty())
                            <span class="badge badge-gray">No Role</span>
                        @endif
                    @endif
                </td>
                <td>
                    @if($user->is_super_admin)
                        <span style="font-size:.78rem;color:#64748b">All permissions</span>
                    @else
                        @php $perms = $user->roles->flatMap->permissions; @endphp
                        <span style="font-size:.78rem;color:#64748b">{{ $perms->unique('id')->count() }} permissions</span>
                    @endif
                </td>
                <td>
                    @if($user->status==='active')
                        <span class="badge badge-success">Active</span>
                    @elseif($user->status==='suspended')
                        <span class="badge badge-danger">Suspended</span>
                    @else
                        <span class="badge badge-gray">{{ ucfirst($user->status) }}</span>
                    @endif
                </td>
                <td style="color:#64748b;font-size:.82rem">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                <td style="display:flex;gap:6px">
                    <button class="btn btn-outline btn-sm" onclick="openEditModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->phone }}', '{{ $user->status }}', '{{ $user->is_super_admin }}', {{ $user->roles->pluck('id')->toJson() }}, {{ $user->roles->flatMap->permissions->pluck('slug')->toJson() }})">Edit</button>
                    <form method="POST" action="{{ route('platform.platform-users.reset-password', $user->id) }}" onsubmit="return confirm('Reset password for {{ $user->name }}?')">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Reset PW</button>
                    </form>
                    @if(!$user->is_super_admin)
                    <form method="POST" action="{{ route('platform.platform-users.destroy', $user->id) }}" onsubmit="return confirm('Delete {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:40px">No platform users</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $users->withQueryString()->links() }}
    </div>
</div>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>Add Platform User</h3>
            <button onclick="closeModal('createUserModal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" action="{{ route('platform.platform-users.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input class="form-input" type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input class="form-input" type="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input class="form-input" type="text" name="phone">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input class="form-input" type="password" name="password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input class="form-input" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select class="form-select" name="role_id" required id="create_role_select">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }} — {{ $role->permissions->count() }} permissions</option>
                        @endforeach
                    </select>
                </div>

                <!-- Granular Permissions -->
                <div class="form-group">
                    <label class="form-label" style="margin-bottom:8px">Granular Permissions</label>
                    <p style="font-size:.78rem;color:#94a3b8;margin-bottom:12px">Select a role first, then customize individual permissions below.</p>

                    @php
                        $groupedPerms = $allPermissions->groupBy('group');
                    @endphp

                    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;max-height:320px;overflow-y:auto">
                        @foreach($groupedPerms as $group => $perms)
                        <div style="border-bottom:1px solid #f1f5f9">
                            <div style="padding:10px 14px;background:#f8fafc;font-size:.8rem;font-weight:700;color:#475569;text-transform:capitalize;display:flex;justify-content:space-between;align-items:center;cursor:pointer" onclick="toggleGroup(this)">
                                <span>{{ str_replace('_', ' ', $group) }}</span>
                                <span style="font-size:.7rem;color:#94a3b8">▼</span>
                            </div>
                            <div style="padding:6px 14px 10px">
                                @foreach($perms as $perm)
                                <label class="form-check" style="padding:4px 0">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}" class="perm-check" data-group="{{ $group }}">
                                    <span style="font-size:.82rem">{{ str_replace('platform.' . $group . '.', '', $perm->slug) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllPerms(true)">Select All</button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllPerms(false)">Deselect All</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>Edit Platform User</h3>
            <button onclick="closeModal('editUserModal')" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" id="editUserForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input class="form-input" type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-input" type="email" id="edit_email" disabled style="background:#f1f5f9">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input class="form-input" type="text" name="phone" id="edit_phone">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Super Admin</label>
                        <select class="form-select" name="is_super_admin" id="edit_super_admin">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role_id" id="edit_role_select">
                        <option value="">No Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }} — {{ $role->permissions->count() }} permissions</option>
                        @endforeach
                    </select>
                </div>

                <!-- Granular Permissions -->
                <div class="form-group">
                    <label class="form-label" style="margin-bottom:8px">Granular Permissions</label>
                    <p style="font-size:.78rem;color:#94a3b8;margin-bottom:12px">Override individual permissions for this user.</p>

                    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;max-height:320px;overflow-y:auto">
                        @foreach($groupedPerms as $group => $perms)
                        <div style="border-bottom:1px solid #f1f5f9">
                            <div style="padding:10px 14px;background:#f8fafc;font-size:.8rem;font-weight:700;color:#475569;text-transform:capitalize;display:flex;justify-content:space-between;align-items:center;cursor:pointer" onclick="toggleGroup(this)">
                                <span>{{ str_replace('_', ' ', $group) }}</span>
                                <span style="font-size:.7rem;color:#94a3b8">▼</span>
                            </div>
                            <div style="padding:6px 14px 10px">
                                @foreach($perms as $perm)
                                <label class="form-check" style="padding:4px 0">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}" class="perm-check edit-perm-check" data-group="{{ $group }}">
                                    <span style="font-size:.82rem">{{ str_replace('platform.' . $group . '.', '', $perm->slug) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllEditPerms(true)">Select All</button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllEditPerms(false)">Deselect All</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rolePermissions = @json($roles->mapWithKeys(fn($r) => [$r->id => $r->permissions->pluck('slug')->values()->all()]));

    // Auto-check permissions when role is selected (create form)
    document.getElementById('create_role_select').addEventListener('change', function() {
        const roleId = this.value;
        const perms = rolePermissions[roleId] || [];
        document.querySelectorAll('#createUserModal .perm-check').forEach(cb => {
            cb.checked = perms.includes(cb.value);
        });
    });

    // Auto-check permissions when role is selected (edit form)
    document.getElementById('edit_role_select').addEventListener('change', function() {
        const roleId = this.value;
        const perms = rolePermissions[roleId] || [];
        document.querySelectorAll('#editUserModal .edit-perm-check').forEach(cb => {
            cb.checked = perms.includes(cb.value);
        });
    });

    function toggleGroup(el) {
        const body = el.nextElementSibling;
        const arrow = el.querySelector('span:last-child');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            arrow.textContent = '▼';
        } else {
            body.style.display = 'none';
            arrow.textContent = '▶';
        }
    }

    function toggleAllPerms(checked) {
        document.querySelectorAll('#createUserModal .perm-check').forEach(cb => cb.checked = checked);
    }

    function toggleAllEditPerms(checked) {
        document.querySelectorAll('#editUserModal .edit-perm-check').forEach(cb => cb.checked = checked);
    }

    function openEditModal(id, name, email, phone, status, isSuperAdmin, roleIds, permSlugs) {
        document.getElementById('editUserForm').action = '/platform/platform-users/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone || '';
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_super_admin').value = isSuperAdmin ? '1' : '0';

        // Set role
        const roleSelect = document.getElementById('edit_role_select');
        roleSelect.value = roleIds.length > 0 ? roleIds[0] : '';

        // Set permissions
        document.querySelectorAll('#editUserModal .edit-perm-check').forEach(cb => {
            cb.checked = permSlugs.includes(cb.value);
        });

        openModal('editUserModal');
    }
</script>
@endpush
