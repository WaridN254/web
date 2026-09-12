<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PlatformSettingController extends Controller
{
    public function index()
    {
        $this->ensureEmailSettingsExist();

        $settings = PlatformSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        $emailSettings = $settings->pull('email', collect());

        $currentMailer = config('mail.default');
        $mailerStatus = $currentMailer === 'log' ? 'development' : 'production';
        $queueDriver = config('queue.default');

        return view('platform.settings.index', compact('settings', 'emailSettings', 'mailerStatus', 'currentMailer', 'queueDriver'));
    }

    private function ensureEmailSettingsExist(): void
    {
        $defaults = [
            // Email settings
            ['key' => 'mail_mailer', 'value' => 'log', 'type' => 'select', 'group' => 'email', 'options' => ['log' => 'Log (Development)', 'smtp' => 'SMTP']],
            ['key' => 'mail_host', 'value' => '', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'password', 'group' => 'email'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'select', 'group' => 'email', 'options' => ['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'null' => 'None']],
            ['key' => 'mail_from_address', 'value' => 'no-reply@halis.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_from_name', 'value' => 'HALIS', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_reply_to_address', 'value' => 'support@halis.com', 'type' => 'text', 'group' => 'email'],
            // General settings
            ['key' => 'platform_name', 'value' => 'HALIS SaaS', 'type' => 'text', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@halis.com', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'UGX', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_timezone', 'value' => 'Africa/Kampala', 'type' => 'text', 'group' => 'general'],
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],
            // Subscription settings
            ['key' => 'default_trial_days', 'value' => '14', 'type' => 'integer', 'group' => 'subscription'],
            ['key' => 'default_grace_period_days', 'value' => '7', 'type' => 'integer', 'group' => 'subscription'],
        ];

        foreach ($defaults as $def) {
            PlatformSetting::firstOrCreate(
                ['key' => $def['key']],
                $def
            );
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = PlatformSetting::where('key', $key)->first();
            if ($setting && $setting->type === 'password' && empty($value)) {
                continue;
            }
            PlatformSetting::set($key, $value);
        }

        PlatformAuditLog::log('platform_settings_updated', null, null, 'PlatformSetting', null, 'Platform settings updated');

        return redirect()->route('platform.settings.index')
            ->with('success', 'Settings updated successfully. Mail transport changes take effect after queue worker restart.');
    }

    public function restartQueue()
    {
        Artisan::call('queue:restart');

        PlatformAuditLog::log('queue_restarted', null, null, 'PlatformSetting', null, 'Queue worker restarted via platform settings');

        return redirect()->route('platform.settings.index')
            ->with('success', 'Queue worker restart signal sent. Workers will restart on their next job processing cycle.');
    }
}
