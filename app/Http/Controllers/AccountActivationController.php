<?php

namespace App\Http\Controllers;

use App\Services\ActivationTokenService;
use App\Services\OnboardingService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class AccountActivationController extends Controller
{
    public function __construct(
        private ActivationTokenService $tokenService,
        private OnboardingService $onboardingService,
    ) {}

    public function showForm(string $token)
    {
        $activation = $this->tokenService->findValid($token);

        if (!$activation) {
            $existing = \App\Models\AccountActivation::findByToken($token);

            if ($existing && $existing->used_at) {
                return view('auth.activation-error', [
                    'title' => 'Link Already Used',
                    'message' => 'This setup link has already been used.',
                    'show_login' => true,
                ]);
            }

            if ($existing && $existing->expires_at->isPast()) {
                return view('auth.activation-error', [
                    'title' => 'Link Expired',
                    'message' => 'This account setup link has expired.',
                    'show_resend' => true,
                    'email' => $existing->email,
                ]);
            }

            return view('auth.activation-error', [
                'title' => 'Invalid Link',
                'message' => 'This account setup link is invalid.',
            ]);
        }

        return view('auth.create-account', [
            'token' => $token,
            'email' => $activation->email,
            'business_name' => $activation->tenant->name,
        ]);
    }

    public function createAccount(Request $request, string $token)
    {
        $rateLimitKey = 'activate:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'password' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $activation = $this->tokenService->findValid($token);

        if (!$activation) {
            return redirect()->route('registration.show')
                ->with('error', 'Invalid or expired link. Please register again.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        RateLimiter::hit($rateLimitKey, 300);

        DB::transaction(function () use ($request, $activation) {
            $tenant = $activation->tenant;
            $ownerRole = \App\Models\Role::updateOrCreate(
                ['name' => 'owner'],
                [
                    'can_manage_products' => true,
                    'can_manage_variants' => true,
                    'can_manage_services' => true,
                    'can_manage_stock' => true,
                    'can_view_reports' => true,
                    'can_manage_discounts' => true,
                    'can_manage_payment_methods' => true,
                    'can_manage_tax_efris' => true,
                    'can_manage_document_vault' => true,
                    'can_manage_compliance' => true,
                    'can_manage_customers' => true,
                    'can_manage_users' => true,
                    'can_cash_in_out' => true,
                    'can_process_refunds' => true,
                    'can_delete_sales' => true,
                    'can_view_sales_history' => true,
                    'can_settle_credit_payments' => true,
                    'can_manage_quotations' => true,
                    'can_manage_warranties' => true,
                    'can_end_day' => true,
                    'can_backup_data' => true,
                    'can_restore_data' => true,
                    'can_run_cloud_resync' => true,
                    'can_edit_settings' => true,
                    'can_manage_configurations' => true,
                    'can_override_serial_verification' => true,
                    'can_override_credit_limit' => true,
                    'can_access_admin_dashboard' => true,
                    'can_access_sales_dashboard' => true,
                    'can_access_dashboard_2' => true,
                    'can_view_email' => true,
                    'can_compose_email' => true,
                    'can_send_email' => true,
                    'can_delete_email' => true,
                    'can_manage_email_accounts' => true,
                    'can_manage_email_labels' => true,
                    'can_manage_email_templates' => true,
                    'can_sync_email' => true,
                    'can_view_localization_settings' => true,
                    'can_manage_localization_settings' => true,
                    'can_manage_languages' => true,
                    'can_manage_translations' => true,
                    'can_manage_currencies' => true,
                    'can_manage_exchange_rates' => true,
                ]
            );

            $user = User::create([
                'full_name' => $request->name,
                'email' => $activation->email,
                'password_hash' => Hash::make($request->password),
                'role_id' => $ownerRole?->id,
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'language' => 'en',
                'locale' => 'en',
                'timezone' => $tenant->settings['timezone'] ?? 'Africa/Kampala',
            ]);

            $this->onboardingService->createFreeSubscription($tenant->id);
            $this->onboardingService->getOrCreateForTenant($tenant->id);

            $this->tokenService->markUsed($activation);
        });

        $user = User::where('email', $activation->email)
            ->where('tenant_id', $activation->tenant_id)
            ->first();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.index');
    }
}
