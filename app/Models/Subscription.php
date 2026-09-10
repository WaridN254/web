<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'subscriptions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'billing_cycle', 'price', 'currency',
        'trial_starts_at', 'trial_ends_at', 'starts_at', 'ends_at',
        'cancelled_at', 'grace_period_ends_at', 'payment_method', 'external_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isTrial(): bool
    {
        return $this->status === 'trialing';
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'cancelled') return true;
        if ($this->ends_at && $this->ends_at->isPast()) return true;
        return false;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays($days))
            ->where('ends_at', '>', now());
    }

    public function scopePastDue($query)
    {
        return $query->where('status', 'past_due');
    }
}
