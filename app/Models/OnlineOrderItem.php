<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrderItem extends Model
{
    use \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'unit_conversion_to_base' => 'decimal:4',
        'base_quantity' => 'decimal:3',
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
