<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineStoreSettings extends Model
{
    use \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_pickup' => 'boolean',
        'allow_delivery' => 'boolean',
        'tax_included' => 'boolean',
        'allow_guest_checkout' => 'boolean',
        'require_phone' => 'boolean',
        'require_address' => 'boolean',
        'show_stock_levels' => 'boolean',
        'allow_branch_selection' => 'boolean',
        'pickup_branches' => 'array',
        'business_hours' => 'array',
        'contact_info' => 'array',
        'delivery_fee' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
