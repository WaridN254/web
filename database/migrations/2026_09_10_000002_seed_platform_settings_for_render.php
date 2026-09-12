<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('platform_settings')) {
            return;
        }

        $settings = [
            ['key' => 'mail_mailer', 'value' => 'smtp', 'type' => 'select', 'group' => 'email', 'options' => json_encode(['log' => 'Log (Development)', 'smtp' => 'SMTP'])],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_username', 'value' => 'waridwalz2@gmail.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_password', 'value' => 'zcqb quok cdif bysg', 'type' => 'password', 'group' => 'email'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'select', 'group' => 'email', 'options' => json_encode(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'null' => 'None'])],
            ['key' => 'mail_from_address', 'value' => 'waridwalz2@gmail.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_from_name', 'value' => 'HALIS', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_reply_to_address', 'value' => 'waridwalz2@gmail.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'platform_name', 'value' => 'HALIS SaaS', 'type' => 'text', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'waridwalz2@gmail.com', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'UGX', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_timezone', 'value' => 'Africa/Kampala', 'type' => 'text', 'group' => 'general'],
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],
            ['key' => 'default_trial_days', 'value' => '14', 'type' => 'integer', 'group' => 'subscription'],
            ['key' => 'default_grace_period_days', 'value' => '7', 'type' => 'integer', 'group' => 'subscription'],
        ];

        foreach ($settings as $s) {
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $s['key']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->whereIn('key', [
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_encryption', 'mail_from_address', 'mail_from_name', 'mail_reply_to_address',
            'platform_name', 'support_email', 'default_currency', 'default_timezone',
            'registration_enabled', 'maintenance_mode', 'default_trial_days', 'default_grace_period_days',
        ])->delete();
    }
};
