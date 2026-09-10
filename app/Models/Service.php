<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'services';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
