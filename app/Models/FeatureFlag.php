<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'feature_flags';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'slug', 'description', 'is_enabled', 'scope', 'config'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
        ];
    }

    public function tenantFeatureFlags()
    {
        return $this->hasMany(TenantFeatureFlag::class);
    }

    public function isEnabledFor(?string $tenantId = null): bool
    {
        if (!$this->is_enabled) return false;
        if (!$tenantId) return true;
        $tenantFlag = $this->tenantFeatureFlags()->where('tenant_id', $tenantId)->first();
        if ($tenantFlag) return $tenantFlag->is_enabled;
        return true;
    }
}
