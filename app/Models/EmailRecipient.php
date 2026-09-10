<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRecipient extends Model
{
    protected $table = 'email_recipients';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $fillable = [
        'email_id',
        'tenant_id',
        'type',
        'name',
        'email_address',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'email_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
