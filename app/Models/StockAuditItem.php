<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockAuditItem extends Model
{
    use HasFactory, BelongsToTenant, HasUuids;

    public const UPDATED_AT = null;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'audit_id',
        'product_id',
        'product_name',
        'system_qty',
        'physical_qty',
        'discrepancy',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'physical_qty' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    public function audit()
    {
        return $this->belongsTo(StockAudit::class, 'audit_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
