<?php

namespace App\Services;

use App\Mail\AccountActivationMail;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private ActivationTokenService $tokenService;

    public function __construct(ActivationTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function sendActivationEmail(string $tenantId, string $email, ?string $name, string $businessName): bool
    {
        $plainToken = $this->tokenService->create($tenantId, $email, $name);
        $activationUrl = $this->tokenService->getActivationUrl($plainToken);
        $expiresHours = $this->tokenService->getExpirationHours();

        try {
            $mail = new AccountActivationMail($businessName, $activationUrl, $expiresHours);

            Mail::to($email)->send($mail);

            $this->logEmail([
                'tenant_id' => $tenantId,
                'recipient' => $email,
                'type' => 'account_activation',
                'status' => 'sent',
            ]);

            Log::info('Activation email sent', [
                'tenant_id' => $tenantId,
                'email' => $email,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logEmail([
                'tenant_id' => $tenantId,
                'recipient' => $email,
                'type' => 'account_activation',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            Log::error('Activation email failed', [
                'tenant_id' => $tenantId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function queueActivationEmail(string $tenantId, string $email, ?string $name, string $businessName): bool
    {
        $plainToken = $this->tokenService->create($tenantId, $email, $name);
        $activationUrl = $this->tokenService->getActivationUrl($plainToken);
        $expiresHours = $this->tokenService->getExpirationHours();

        try {
            $mail = new AccountActivationMail($businessName, $activationUrl, $expiresHours);

            Mail::to($email)->queue($mail);

            $this->logEmail([
                'tenant_id' => $tenantId,
                'recipient' => $email,
                'type' => 'account_activation',
                'status' => 'queued',
            ]);

            Log::info('Activation email queued', [
                'tenant_id' => $tenantId,
                'email' => $email,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logEmail([
                'tenant_id' => $tenantId,
                'recipient' => $email,
                'type' => 'account_activation',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            Log::error('Activation email queue failed', [
                'tenant_id' => $tenantId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function resendActivationEmail(string $tenantId, string $email, string $businessName): bool
    {
        $activation = \App\Models\AccountActivation::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$activation) {
            Log::warning('Resend activation: no pending activation found', [
                'tenant_id' => $tenantId,
                'email' => $email,
            ]);
            return false;
        }

        $this->tokenService->invalidateExisting($tenantId, $email);

        return $this->sendActivationEmail(
            $tenantId,
            $email,
            $activation->name,
            $businessName
        );
    }

    private function logEmail(array $data): void
    {
        try {
            EmailLog::create($data);
        } catch (\Exception $e) {
            Log::warning('Failed to log email event', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }
}
