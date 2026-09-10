<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
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

    protected $table = 'tax_categories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}
