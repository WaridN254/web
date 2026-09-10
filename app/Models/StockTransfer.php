<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StockTransfer extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'stock_transfers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $transfer) {
            if (empty($transfer->transfer_number)) {
                $transfer->transfer_number = 'TRF-' . strtoupper(Str::random(8));
            }
        });
    }

    // ─── Constants ──────────────────────────────────────────

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    // ─── Relationships ──────────────────────────────────────

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(StockTransferSerial::class, 'transfer_id');
    }

    // ─── Helpers ────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_IN_TRANSIT => 'primary',
            self::STATUS_RECEIVED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }
}
