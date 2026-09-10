<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'purchase_items';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'expiry_date' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            // Auto-fill product_name and sku from the product
            if ($item->product_id && (empty($item->product_name))) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $item->product_name = $product->name;
                    $item->sku = $product->barcode ?? null;
                }
            }

            $subtotal = floatval($item->quantity) * floatval($item->unit_cost);
            $item->subtotal = $subtotal;
            $item->total = $subtotal - floatval($item->discount) + floatval($item->tax);
        });

        static::saved(function ($item) {
            $item->purchaseOrder?->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->purchaseOrder?->recalculateTotals();
        });
    }
}
