<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLabelEmail extends Model
{
    protected $table = 'email_label_email';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $fillable = [
        'email_id',
        'email_label_id',
        'tenant_id',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function emailLabel(): BelongsTo
    {
        return $this->belongsTo(EmailLabel::class, 'email_label_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
