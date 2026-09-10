<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSaleUnit extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'product_sale_units';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'unit_name',
        'unit_label',
        'conversion_to_base_unit',
        'price',
        'allow_decimal',
        'is_default',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'conversion_to_base_unit' => 'decimal:2',
        'price' => 'decimal:2',
        'allow_decimal' => 'boolean',
        'is_default' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}