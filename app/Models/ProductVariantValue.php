<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantValue extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'product_variant_values';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'variant_id',
        'attribute_id',
        'attribute_value_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(VariantAttributeValue::class, 'attribute_value_id');
    }
}
