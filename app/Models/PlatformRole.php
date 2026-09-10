<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlatformRole extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform_roles';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'slug', 'description', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PlatformPermission::class, 'platform_role_permissions', 'role_id', 'permission_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(PlatformUser::class, 'platform_user_roles', 'role_id', 'user_id');
    }
}
