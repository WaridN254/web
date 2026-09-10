<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantFeatureFlag extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tenant_feature_flags';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['tenant_id', 'feature_flag_id', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function featureFlag()
    {
        return $this->belongsTo(FeatureFlag::class);
    }
}
