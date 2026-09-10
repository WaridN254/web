<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineCart extends Model
{
    use \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
