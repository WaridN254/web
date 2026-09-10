<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'branch_stocks';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'minimum_stock' => 'decimal:4',
        'maximum_stock' => 'decimal:4',
        'average_cost' => 'decimal:4',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getAvailableQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved_quantity;
    }

    public function getIsLowStockAttribute(): bool
    {
        if (is_null($this->minimum_stock)) {
            return false;
        }

        return $this->quantity <= $this->minimum_stock;
    }
}
