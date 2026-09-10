<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ComplianceReminder extends Model
{
    use HasUuids, BelongsToTenant;

    public function getRouteKeyName(): string
    {
        return 'title';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->whereRaw('LOWER(title) = ?', [strtolower((string) $value)])
            ->first();
    }

    protected $table = 'compliance_reminders';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'datetime',
        'amount' => 'decimal:2',
        'status' => 'string', // 'pending', 'completed', 'overdue'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_OVERDUE = 'overdue';
}
