<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user instanceof \App\Models\User) {
            $tenant = $user->tenant;
            if ($tenant && $tenant->status === 'onboarding') {
                if (!$request->is('onboarding*') && !$request->is('logout')) {
                    return redirect()->route('onboarding.index');
                }
            }
        }

        return $next($request);
    }
}
