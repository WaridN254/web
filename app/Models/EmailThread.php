<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailThread extends Model
{
    protected $table = 'email_threads';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'is_muted' => 'boolean',
        'last_message_at' => 'datetime',
        'message_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'thread_id');
    }

    public function scopeForFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }
}
