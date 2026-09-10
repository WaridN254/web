<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\Session;

class ImpersonationService
{
    public function startImpersonation(string $platformUserId, string $targetTenantId, string $targetUserId): void
    {
        $platformUser = auth('platform')->user();
        $targetUser = User::where('id', $targetUserId)->where('tenant_id', $targetTenantId)->firstOrFail();

        Session::put('impersonation', [
            'platform_user_id' => $platformUser->id,
            'platform_user_name' => $platformUser->name,
            'target_tenant_id' => $targetTenantId,
            'target_user_id' => $targetUserId,
            'target_user_name' => $targetUser->full_name ?? $targetUser->name,
            'started_at' => now()->toIso8601String(),
        ]);

        $targetTenant = Tenant::find($targetTenantId);

        PlatformAuditLog::log(
            'business_impersonated',
            $platformUser->id,
            $targetTenantId,
            'User',
            $targetUserId,
            "Impersonating user '{$targetUser->full_name ?? $targetUser->name}' in tenant '{$targetTenant?->name ?? $targetTenantId}'"
        );
    }

    public function stopImpersonation(): void
    {
        Session::forget('impersonation');
    }

    public function isImpersonating(): bool
    {
        return Session::has('impersonation');
    }

    public function getImpersonationData(): ?array
    {
        return Session::get('impersonation');
    }
}
