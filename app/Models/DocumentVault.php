<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentVault extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'document_vault';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'expiry_date' => 'datetime',
        'uploaded_at' => 'datetime',
    ];
}
