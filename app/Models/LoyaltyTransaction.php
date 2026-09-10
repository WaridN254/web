<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyTransaction extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'points' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
