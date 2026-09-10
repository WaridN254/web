<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformNotification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform_notifications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['platform_user_id', 'type', 'title', 'message', 'data', 'is_read', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_read' => 'boolean', 'read_at' => 'datetime'];
    }

    public function platformUser(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class);
    }

    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'business_created' => 'heroicon-o-building-office-2',
            'subscription_expiring' => 'heroicon-o-clock',
            'payment_failed' => 'heroicon-o-exclamation-triangle',
            'business_suspended' => 'heroicon-o-no-symbol',
            'default' => 'heroicon-o-bell',
        };
    }
}
