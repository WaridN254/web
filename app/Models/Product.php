<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'products';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'billing_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'track_stock' => 'boolean',
        'track_serial_numbers' => 'boolean',
        'has_variants' => 'boolean',
        'price_change_allowed' => 'boolean',
        'warranty_enabled' => 'boolean',
        'is_deleted' => 'boolean',
        'is_bundle' => 'boolean',
        'expiration_date' => 'date',
        'unit_conversions' => 'array',
        'sale_units' => 'array',
        'price_per_unit' => 'array',
        'allow_decimal_per_unit' => 'array',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function variantAttributes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(VariantAttribute::class, 'product_variant_attributes', 'product_id', 'attribute_id');
    }

    public function saleUnits(): HasMany
    {
        return $this->hasMany(ProductSaleUnit::class, 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class, 'product_id');
    }

    public function availableSerialCount(): int
    {
        if ($this->has_variants) {
            if ($this->relationLoaded('variants')) {
                return $this->variants->sum(fn ($v) => $v->available_stock);
            }
            return $this->variants()->sum(fn ($v) => $v->track_serial_numbers
                ? $v->serials()->where('status', 'available')->count()
                : $v->current_stock);
        }

        if (! $this->track_serial_numbers) {
            return (int) $this->current_stock;
        }

        if ($this->relationLoaded('serials')) {
            return $this->serials->where('status', 'available')->count();
        }

        if (isset($this->available_serial_count)) {
            return (int) $this->available_serial_count;
        }

        return $this->serials()->where('status', 'available')->count();
    }

    /**
     * Get stock for this product at a specific branch.
     */
    public function getStockAtBranch(string $branchId): float
    {
        return app(\App\Services\InventoryService::class)->getProductStock($this->id, $branchId);
    }

    /**
     * Get stock for a variant at a specific branch.
     */
    public function getVariantStockAtBranch(string $variantId, string $branchId): float
    {
        return app(\App\Services\InventoryService::class)->getProductStock($this->id, $branchId, $variantId);
    }

    public function getStockQuantityAttribute(): int
    {
        return $this->availableSerialCount();
    }

    public function imageUrl(): ?string
    {
        foreach ($this->images as $image) {
            $url = $image->image_path
                ? url('product-images/' . $image->image_path)
                : $image->image_url;

            if ($url) {
                return $url;
            }
        }

        if ($this->image_url) {
            return $this->image_url;
        }

        return null;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }

    public function delete()
    {
        $this->is_deleted = true;
        return $this->save();
    }

}
