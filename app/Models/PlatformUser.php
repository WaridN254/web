<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformUser extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'platform_users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'is_super_admin',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(PlatformRole::class, 'platform_user_roles', 'user_id', 'role_id');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(PlatformPermission::class, 'platform_user_permissions', 'user_id', 'permission_id');
    }

    public function hasRole(string $roleSlug): bool
    {
        if ($this->is_super_admin) return true;
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->is_super_admin) return true;

        // Check direct user permissions first (overrides)
        if ($this->directPermissions()->where('slug', $permissionSlug)->exists()) {
            return true;
        }

        // Then check role-based permissions
        return $this->roles()->whereHas('permissions', function ($q) use ($permissionSlug) {
            $q->where('slug', $permissionSlug);
        })->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) return true;
        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) return true;
        }
        return false;
    }

    public function getAllPermissionSlugs(): array
    {
        if ($this->is_super_admin) {
            return PlatformPermission::pluck('slug')->toArray();
        }

        $rolePerms = $this->roles->flatMap->permissions->pluck('slug')->toArray();
        $directPerms = $this->directPermissions->pluck('slug')->toArray();
        return array_unique(array_merge($rolePerms, $directPerms));
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'gray',
            'suspended' => 'danger',
            default => 'gray',
        };
    }
}
