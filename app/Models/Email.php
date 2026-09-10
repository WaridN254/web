<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    protected $table = 'emails';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'body_html' => 'string',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'is_draft' => 'boolean',
        'is_muted' => 'boolean',
        'has_attachments' => 'boolean',
        'attachment_count' => 'integer',
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function getFromDisplayAttribute(): ?string
    {
        return $this->from_name ?? $this->from_address;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(EmailThread::class, 'thread_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class, 'email_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class, 'email_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(EmailLabel::class, 'email_label_email', 'email_id', 'email_label_id')
            ->withPivot('tenant_id');
    }

    public function toRecipients()
    {
        return $this->recipients()->where('type', 'to');
    }

    public function ccRecipients()
    {
        return $this->recipients()->where('type', 'cc');
    }

    public function bccRecipients()
    {
        return $this->recipients()->where('type', 'bcc');
    }
}
