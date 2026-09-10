<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformUser;
use App\Models\PlatformRole;
use App\Models\PlatformPermission;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformUserController extends Controller
{
    public function index(Request $request)
    {
        $query = PlatformUser::with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(25);
        $roles = PlatformRole::with('permissions')->orderBy('name')->get();
        $allPermissions = PlatformPermission::orderBy('group')->orderBy('slug')->get();

        return view('platform.users.index', compact('users', 'roles', 'allPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:platform_users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:platform_roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:platform_permissions,slug',
        ]);

        $user = PlatformUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->roles()->attach($request->role_id);

        // Sync granular permissions via direct table inserts
        if ($request->permissions) {
            $this->syncPermissions($user, $request->permissions);
        }

        PlatformAuditLog::log('platform_user_created', null, null, 'PlatformUser', $user->id, "Platform user '{$user->name}' created");

        return redirect()->route('platform.platform-users.index')
            ->with('success', 'Platform user created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $user = PlatformUser::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'sometimes|in:active,inactive,suspended',
            'is_super_admin' => 'sometimes|boolean',
            'role_id' => 'sometimes|exists:platform_roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:platform_permissions,slug',
        ]);

        if (isset($data['is_super_admin'])) {
            $data['is_super_admin'] = (bool) $data['is_super_admin'];
        }

        if (isset($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
            unset($data['role_id']);
        }

        $user->update($data);

        // Sync granular permissions
        if ($request->has('permissions')) {
            $this->syncPermissions($user, $request->permissions ?? []);
        }

        PlatformAuditLog::log('platform_user_updated', null, null, 'PlatformUser', $user->id, "Platform user '{$user->name}' updated");

        return redirect()->route('platform.platform-users.index')
            ->with('success', 'Platform user updated successfully.');
    }

    public function resetPassword(string $id)
    {
        $user = PlatformUser::findOrFail($id);
        $newPassword = Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        PlatformAuditLog::log('platform_user_password_reset', null, null, 'PlatformUser', $user->id, "Password reset for platform user '{$user->name}'");

        return redirect()->route('platform.platform-users.index')
            ->with('success', "Password reset. New password: {$newPassword}");
    }

    public function destroy(string $id)
    {
        $user = PlatformUser::findOrFail($id);
        if ($user->is_super_admin) {
            return redirect()->route('platform.platform-users.index')
                ->with('error', 'Cannot delete a Super Admin.');
        }
        $user->delete();
        return redirect()->route('platform.platform-users.index')
            ->with('success', 'Platform user deleted.');
    }

    private function syncPermissions(PlatformUser $user, array $permissionSlugs): void
    {
        // Clear existing user-level permission overrides
        \DB::table('platform_user_permissions')->where('user_id', $user->id)->delete();

        $permIds = PlatformPermission::whereIn('slug', $permissionSlugs)->pluck('id');
        foreach ($permIds as $permId) {
            \DB::table('platform_user_permissions')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
