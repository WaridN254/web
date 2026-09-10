<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductWarranty extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'product_warranties';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'transaction_id',
        'transaction_item_id',
        'product_id',
        'product_name',
        'serial_id',
        'serial_number',
        'customer_id',
        'customer_name',
        'receipt_number',
        'provider',
        'warranty_start',
        'warranty_end',
        'guarantee_end',
        'status',
        'notes',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'guarantee_end' => 'date',
        'last_synced_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class, 'warranty_id');
    }
}
