<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailLabel extends Model
{
    protected $table = 'email_labels';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(Email::class, 'email_label_email', 'email_label_id', 'email_id')
            ->withPivot('tenant_id');
    }
}
