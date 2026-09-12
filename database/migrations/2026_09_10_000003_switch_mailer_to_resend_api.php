<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('platform_settings')) {
            return;
        }

        // Switch mailer from smtp to resend
        DB::table('platform_settings')->where('key', 'mail_mailer')->update([
            'value' => 'resend',
            'options' => json_encode(['log' => 'Log (Development)', 'resend' => 'Resend API', 'smtp' => 'SMTP']),
            'updated_at' => now(),
        ]);

        // Add Resend API key placeholder (empty — must be set via Platform Settings UI or env)
        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'mail_api_key'],
            ['id' => (string) Str::uuid(), 'value' => '', 'type' => 'password', 'group' => 'email', 'created_at' => now(), 'updated_at' => now()]
        );

        // Set from address to Resend's default verified sender (no domain verification needed)
        DB::table('platform_settings')->where('key', 'mail_from_address')->update([
            'value' => 'onboarding@resend.dev',
            'updated_at' => now(),
        ]);

        // Clear SMTP-specific settings that are no longer needed
        DB::table('platform_settings')->whereIn('key', ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption'])->update([
            'value' => '',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'mail_mailer')->update([
            'value' => 'smtp',
            'options' => json_encode(['log' => 'Log (Development)', 'smtp' => 'SMTP']),
            'updated_at' => now(),
        ]);

        DB::table('platform_settings')->where('key', 'mail_from_address')->update([
            'value' => 'no-reply@halis.com',
            'updated_at' => now(),
        ]);

        DB::table('platform_settings')->where('key', 'mail_api_key')->delete();
    }
};
