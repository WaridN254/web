<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasUuids, BelongsToTenant;

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->whereRaw('LOWER(name) = ?', [strtolower((string) $value)])
            ->first();
    }

    protected $table = 'discounts';

    public $timestamps = false;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'type' => 'string',
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
    ];
}
