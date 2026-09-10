<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth('platform')->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('platform.login');
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
                abort(403, 'Unauthorized. You do not have the required permission: ' . $permission);
            }
        }

        return $next($request);
    }
}
