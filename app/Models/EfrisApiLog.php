<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EfrisApiLog extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'efris_api_logs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'request_data' => 'json',
        'response_data' => 'json',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];
}
