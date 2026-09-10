<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttributeValue extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'variant_attribute_values';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'attribute_id',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }
}
