<?php

namespace App\Services;

use App\Models\AccountActivation;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivationTokenService
{
    private int $expirationMinutes;

    public function __construct()
    {
        $this->expirationMinutes = (int) config('email.activation_expiration', 1440);
    }

    public function create(string $tenantId, string $email, ?string $name = null): string
    {
        $this->invalidateExisting($tenantId, $email);

        $plainToken = AccountActivation::generateToken();
        $hashedToken = hash('sha256', $plainToken);

        AccountActivation::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'name' => $name,
            'token_hash' => $hashedToken,
            'expires_at' => now()->addMinutes($this->expirationMinutes),
        ]);

        Log::info('Activation token created', [
            'tenant_id' => $tenantId,
            'email' => $email,
            'expires_in_minutes' => $this->expirationMinutes,
        ]);

        return $plainToken;
    }

    public function findValid(string $plainToken): ?AccountActivation
    {
        $activation = AccountActivation::findByToken($plainToken);

        if (!$activation) {
            return null;
        }

        if (!$activation->isValid()) {
            return null;
        }

        return $activation;
    }

    public function markUsed(AccountActivation $activation): void
    {
        $activation->markUsed();

        Log::info('Activation token used', [
            'tenant_id' => $activation->tenant_id,
            'email' => $activation->email,
        ]);
    }

    public function invalidateExisting(string $tenantId, string $email): void
    {
        AccountActivation::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function getActivationUrl(string $plainToken): string
    {
        return route('activation.show', $plainToken);
    }

    public function getExpirationHours(): int
    {
        return (int) ceil($this->expirationMinutes / 60);
    }
}
