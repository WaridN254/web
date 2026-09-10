<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSerial extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'product_serials';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'serial_number',
        'box_serial_number',
        'lot_number',
        'material_code',
        'model_number',
        'purchase_id',
        'supplier_id',
        'status',
        'cost_price',
        'selling_price',
        'received_at',
        'sold_at',
        'sale_id',
        'notes',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'received_at' => 'datetime',
        'sold_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductSerial $serial) {
            if ($serial->product_id && !$serial->cost_price && !$serial->selling_price) {
                $product = \App\Models\Product::find($serial->product_id);
                if ($product) {
                    $serial->cost_price = $product->cost_price;
                    $serial->selling_price = $product->billing_price;
                }
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}