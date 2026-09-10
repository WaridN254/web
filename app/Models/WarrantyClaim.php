<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WarrantyClaim extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $table = 'warranty_claims';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'warranty_id',
        'claim_number',
        'issue_description',
        'status',
        'received_at',
        'resolved_at',
        'resolution_notes',
        'technician_notes',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function warranty()
    {
        return $this->belongsTo(ProductWarranty::class, 'warranty_id');
    }
}
