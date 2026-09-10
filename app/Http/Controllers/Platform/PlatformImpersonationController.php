<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\ImpersonationService;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformImpersonationController extends Controller
{
    public function __construct(
        private ImpersonationService $impersonationService,
    ) {}

    public function start(Request $request, string $tenantId, string $userId)
    {
        $user = auth('platform')->user();
        if (!$user->hasPermission('platform.impersonation.use') && !$user->is_super_admin) {
            abort(403, 'You do not have permission to impersonate users.');
        }

        $targetUser = User::where('id', $userId)->where('tenant_id', $tenantId)->firstOrFail();
        $tenant = Tenant::findOrFail($tenantId);

        $this->impersonationService->startImpersonation($user->id, $tenantId, $userId);

        auth()->login($targetUser);

        return redirect()->route('filament.tenant.pages.dashboard')
            ->with('impersonation_active', true);
    }

    public function stop()
    {
        $this->impersonationService->stopImpersonation();
        auth()->logout();

        return redirect()->route('platform.dashboard');
    }
}
