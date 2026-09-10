<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasUuids;

    protected $table = 'currencies';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'minor_unit' => 'integer',
        'decimal_places' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }
}
