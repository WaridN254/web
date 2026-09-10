<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Tenant;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function __construct(
        private EmailService $emailService,
    ) {}

    public function showForm()
    {
        if (auth()->check()) {
            return redirect('/tenant');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rateLimitKey = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'email' => "Too many registration attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:100',
            'country' => 'required|string|max:2',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            return back()->withErrors(['email' => 'An account with this email already exists. Please login.'])->withInput();
        }

        $existingActivation = \App\Models\AccountActivation::where('email', $request->email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();
        if ($existingActivation) {
            return back()->withErrors(['email' => 'A registration is already pending for this email. Please check your inbox or request a new link.'])->withInput();
        }

        $existingTenant = Tenant::where('name', $request->business_name)->first();
        if ($existingTenant) {
            return back()->withErrors(['business_name' => 'A business with this name is already registered.'])->withInput();
        }

        RateLimiter::hit($rateLimitKey, config('email.registration_rate_limit', 60));

        $tenantId = null;
        $email = $request->email;
        $businessName = $request->business_name;

        DB::transaction(function () use ($request, &$tenantId) {
            $business = Business::create([
                'name' => $request->business_name,
                'phone' => $request->phone,
            ]);

            $tenant = Tenant::create([
                'name' => $request->business_name,
                'slug' => Str::slug($request->business_name) . '-' . Str::random(5),
                'business_id' => $business->id,
                'status' => 'onboarding',
                'settings' => [
                    'business_type' => $request->business_type,
                    'country' => $request->country,
                    'phone' => $request->phone,
                    'currency' => $request->country === 'UG' ? 'UGX' : 'USD',
                    'timezone' => $request->country === 'UG' ? 'Africa/Kampala' : 'America/New_York',
                ],
            ]);

            $tenantId = $tenant->id;
        });

        $this->emailService->queueActivationEmail(
            $tenantId,
            $email,
            null,
            $businessName,
        );

        return redirect()->route('registration.sent')
            ->with('email', $email)
            ->with('business_name', $businessName);
    }

    public function sent()
    {
        return view('auth.check-email');
    }

    public function resend(Request $request)
    {
        $rateLimitKey = 'resend:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->with('error', "Too many resend attempts. Please try again in {$seconds} seconds.");
        }

        $request->validate(['email' => 'required|email']);

        $activation = \App\Models\AccountActivation::where('email', $request->email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$activation) {
            return back()->with('error', 'No pending registration found for this email.');
        }

        RateLimiter::hit($rateLimitKey, config('email.resend_rate_limit', 120));

        $this->emailService->resendActivationEmail(
            $activation->tenant_id,
            $activation->email,
            $activation->tenant->name,
        );

        return back()->with('success', 'A new activation link has been sent.');
    }
}
