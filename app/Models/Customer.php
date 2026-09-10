<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'customers';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_enabled' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(LoyaltyAccount::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(CustomerWallet::class);
    }

    public function delete()
    {
        $this->is_deleted = true;
        return $this->save();
    }
}
