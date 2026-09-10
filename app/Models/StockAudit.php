<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockAudit extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'auditor_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function items()
    {
        return $this->hasMany(StockAuditItem::class, 'audit_id');
    }
}
