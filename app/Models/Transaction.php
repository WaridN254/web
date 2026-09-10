<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'transactions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'transaction_date' => 'datetime',
        'is_deleted' => 'boolean',
        'tenant_id' => 'string',
        'customer_id' => 'string',
        'user_id' => 'string',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\TransactionItem::class);
    }

    public function delete()
    {
        $this->is_deleted = true;

        return $this->save();
    }
}
