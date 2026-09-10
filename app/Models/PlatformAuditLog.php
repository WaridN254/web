<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform_audit_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'platform_user_id', 'tenant_id', 'action', 'resource_type',
        'resource_id', 'description', 'ip_address', 'user_agent', 'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function platformUser(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function log(
        string $action,
        ?string $userId = null,
        ?string $tenantId = null,
        ?string $resourceType = null,
        ?string $resourceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): static {
        return static::create([
            'platform_user_id' => $userId ?? auth('platform')->id(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
