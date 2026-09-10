<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OnlineOrder extends Model
{
    use \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'delivery_address' => 'array',
        'is_deleted' => 'boolean',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OnlineOrderItem::class, 'online_order_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OnlineOrderStatusHistory::class, 'online_order_id');
    }

    public static function generateOrderNumber(): string
    {
        return 'ONL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
