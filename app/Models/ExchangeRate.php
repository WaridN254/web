<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasUuids;

    protected $table = 'exchange_rates';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:8',
        'effective_at' => 'datetime',
    ];

    public function scopeForTenant($query, ?string $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereNull('tenant_id');
        });
    }

    public function scopeLatest($query, string $base, string $target)
    {
        return $query->where('base_currency', strtoupper($base))
                     ->where('target_currency', strtoupper($target))
                     ->orderByDesc('effective_at');
    }
}
