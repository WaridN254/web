<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanQueue extends Model
{
    protected $table = 'scan_queue';
    protected $fillable = [
        'barcode',
        'status',
        'ip_address',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
