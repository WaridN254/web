<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'product_variants';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'sku',
        'barcode',
        'selling_price_mode',
        'custom_selling_price',
        'cost_price_mode',
        'custom_cost_price',
        'current_stock',
        'reorder_level',
        'track_serial_numbers',
        'is_active',
    ];

    protected $casts = [
        'custom_selling_price' => 'decimal:2',
        'custom_cost_price' => 'decimal:2',
        'current_stock' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'track_serial_numbers' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class, 'variant_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'variant_id');
    }

    /**
     * Get effective selling price (inherited from parent or custom).
     */
    public function getEffectiveSellingPriceAttribute(): float
    {
        if ($this->selling_price_mode === 'custom' && $this->custom_selling_price !== null) {
            return (float) $this->custom_selling_price;
        }
        return (float) ($this->product?->billing_price ?? 0);
    }

    /**
     * Get effective cost price (inherited from parent or custom).
     */
    public function getEffectiveCostPriceAttribute(): float
    {
        if ($this->cost_price_mode === 'custom' && $this->custom_cost_price !== null) {
            return (float) $this->custom_cost_price;
        }
        return (float) ($this->product?->cost_price ?? 0);
    }

    /**
     * Get available stock count.
     * For serialized variants, count available serials.
     * For non-serialized, use current_stock.
     */
    public function getAvailableStockAttribute(): float
    {
        if ($this->track_serial_numbers) {
            return $this->serials()->where('status', 'available')->count();
        }
        return (float) $this->current_stock;
    }

    /**
     * Get the variant's attribute combination as a readable string.
     */
    public function getCombinationNameAttribute(): string
    {
        return $this->values
            ->map(fn ($v) => $v->attribute->name . ': ' . $v->value)
            ->implode(' / ');
    }
}
