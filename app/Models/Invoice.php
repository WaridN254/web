<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'invoices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id', 'subscription_id', 'invoice_number', 'status',
        'subtotal', 'tax_amount', 'total', 'amount_paid', 'amount_due',
        'currency', 'billing_reason', 'due_date', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->due_date && $this->due_date->isPast();
    }

    public static function generateNumber(): string
    {
        return 'INV-' . now()->format('Ym') . '-' . strtoupper(Str::random(6));
    }
}
