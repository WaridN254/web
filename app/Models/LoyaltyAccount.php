<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyAccount extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'balance' => 'integer',
        'total_earned' => 'integer',
        'total_redeemed' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'loyalty_account_id');
    }
}
