<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformUser;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PlatformAuthController extends Controller
{
    public function showLogin()
    {
        if (auth('platform')->check()) {
            return redirect()->route('platform.dashboard');
        }
        return view('platform.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = 'platform_login:' . $request->email;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('platform')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::increment($throttleKey);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = Auth::guard('platform')->user();
        if ($user->status !== 'active') {
            Auth::guard('platform')->logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        PlatformAuditLog::log('platform_login', $user->id, null, 'PlatformUser', $user->id, "Platform user '{$user->name}' logged in");

        return redirect()->intended(route('platform.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = auth('platform')->user();
        if ($user) {
            PlatformAuditLog::log('platform_logout', $user->id, null, 'PlatformUser', $user->id, "Platform user '{$user->name}' logged out");
        }

        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
