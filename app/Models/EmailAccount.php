<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAccount extends Model
{
    protected $table = 'email_accounts';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'sync_enabled' => 'boolean',
        'is_active' => 'boolean',
        'unread_count' => 'integer',
    ];

    public function getIsConnectedAttribute(): bool
    {
        return $this->connection_status === 'connected';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(EmailThread::class, 'email_account_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'email_account_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(EmailLabel::class, 'email_account_id');
    }
}
