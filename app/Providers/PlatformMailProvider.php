<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\ServiceProvider;

class PlatformMailProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $this->configureMailer();
        } catch (\Throwable) {
            // Silently fallback if database is not available/migrated yet
        }
    }

    private function configureMailer(): void
    {
        // Env var RESEND_KEY takes highest priority — always use Resend if set
        $envResendKey = env('RESEND_KEY');
        if (!empty($envResendKey)) {
            config([
                'mail.default' => 'resend',
                'services.resend.key' => $envResendKey,
            ]);

            $fromAddress = config('MAIL_FROM_ADDRESS') ?: config('mail.from.address');
            $fromName = config('MAIL_FROM_NAME') ?: config('mail.from.name');
            if ($fromAddress) {
                config(['mail.from' => ['address' => $fromAddress, 'name' => $fromName]]);
            }
            return;
        }

        if (!class_exists(PlatformSetting::class)) {
            return;
        }

        $mailer = PlatformSetting::get('mail_mailer', config('mail.default'));

        if ($mailer === 'log') {
            config(['mail.default' => 'log']);
            return;
        }

        if ($mailer === 'resend') {
            $apiKey = PlatformSetting::get('mail_api_key', '');
            if (empty($apiKey)) {
                $apiKey = config('services.resend.key', '');
            }
            config([
                'mail.default' => 'resend',
                'services.resend.key' => $apiKey,
            ]);

            $fromAddress = PlatformSetting::get('mail_from_address', config('mail.from.address'));
            $fromName = PlatformSetting::get('mail_from_name', config('mail.from.name'));
            if ($fromAddress) {
                config(['mail.from' => ['address' => $fromAddress, 'name' => $fromName]]);
            }
            return;
        }

        if ($mailer !== 'smtp') {
            return;
        }

        $host = PlatformSetting::get('mail_host', '');
        $port = (int) PlatformSetting::get('mail_port', 587);
        $username = PlatformSetting::get('mail_username', '');
        $password = PlatformSetting::get('mail_password', '');
        $encryption = PlatformSetting::get('mail_encryption', 'tls');

        if (empty($host)) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.url' => null,
        ]);

        $fromAddress = PlatformSetting::get('mail_from_address', config('mail.from.address'));
        $fromName = PlatformSetting::get('mail_from_name', config('mail.from.name'));
        $replyTo = PlatformSetting::get('mail_reply_to_address', '');

        if ($fromAddress) {
            config(['mail.from' => ['address' => $fromAddress, 'name' => $fromName]]);
        }
    }
}
