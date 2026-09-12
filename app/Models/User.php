<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;

class User extends Authenticatable implements HasName, HasAvatar, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public function getFilamentName(): string
    {
        return $this->full_name ?? 'User';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar_url);
    }

    /**
     * Get the password for authentication.
     * Support both the default Laravel `password` column and the custom `password_hash` column.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? $this->password ?? '';
    }

    public function getPasswordAttribute(): ?string
    {
        return $this->attributes['password'] ?? $this->attributes['password_hash'] ?? null;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password_hash'] = $value;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'phone',
        'tenant_id',
        'role_id',
        'is_active',
        'avatar_url',
        'language',
        'locale',
        'timezone',
        'default_branch_id',
        'can_view_all_branches',
        'can_manage_branches',
        'can_manage_stock_transfers',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'role_id' => 'string',
            'tenant_id' => 'string',
            'can_view_all_branches' => 'boolean',
            'can_manage_branches' => 'boolean',
            'can_manage_stock_transfers' => 'boolean',
        ];
    }

    public function getRoleIdAttribute($value)
    {
        if (empty($value) || !is_string($value) || strlen($value) < 10) {
            return null;
        }
        return (string) $value;
    }

    public function setRoleIdAttribute($value)
    {
        if (empty($value) || (is_string($value) && strlen($value) < 10)) {
            $this->attributes['role_id'] = null;
        } else {
            $this->attributes['role_id'] = (string) $value;
        }
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) ($this->role?->{$permission} ?? false);
    }

    public function isTenantOwner(): bool
    {
        return strtolower((string) ($this->role?->name ?? '')) === 'owner';
    }

    /**
     * Returns the cashier scope for dashboards: the owner sees the whole
     * tenant, every other user only sees the sales their register made.
     */
    public function scopedCashierId(): ?string
    {
        if ($this->isTenantOwner()) {
            return null;
        }

        return $this->getKey() ? (string) $this->getKey() : null;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')->withDefault([
            'name' => 'Unassigned',
        ]);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function defaultBranch()
    {
        return $this->belongsTo(Branch::class, 'default_branch_id');
    }

    public function assignedBranches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id')
            ->wherePivot('is_primary', true);
    }
}
