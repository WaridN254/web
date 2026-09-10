<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
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

    protected $table = 'payment_methods';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
