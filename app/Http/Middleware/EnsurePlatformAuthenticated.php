<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('platform')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('platform.login');
        }

        $user = auth('platform')->user();
        if ($user->status !== 'active') {
            auth('platform')->logout();
            return redirect()->route('platform.login')->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
