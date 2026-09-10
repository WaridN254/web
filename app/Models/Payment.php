<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'payments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id', 'invoice_id', 'subscription_id', 'payment_number',
        'amount', 'currency', 'status', 'method', 'reference',
        'external_id', 'notes', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function generateNumber(): string
    {
        return 'PAY-' . now()->format('Ym') . '-' . strtoupper(Str::random(6));
    }
}
