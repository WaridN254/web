<?php

namespace App\Filament\Tenant\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class DashboardAwareLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();

        if ($user && $user instanceof \App\Models\User) {
            $tenant = $user->tenant;
            if ($tenant && $tenant->status === 'onboarding') {
                return redirect()->intended(route('onboarding.index'));
            }
        }

        $dashboardUrl = match (true) {
            (bool) ($user?->hasPermission('can_access_admin_dashboard') ?? false) => route('filament.tenant.pages.admin-dashboard'),
            (bool) ($user?->hasPermission('can_access_dashboard_2') ?? false) => route('filament.tenant.pages.admin-dashboard-2'),
            (bool) ($user?->hasPermission('can_access_sales_dashboard') ?? false) => route('filament.tenant.pages.sales-dashboard'),
            default => route('filament.tenant.pages.sales-page'),
        };

        return redirect()->intended($dashboardUrl);
    }
}