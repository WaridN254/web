<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use HasUuids;

    protected $table = 'businesses';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
