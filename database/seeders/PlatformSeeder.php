<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlatformRole;
use App\Models\PlatformPermission;
use App\Models\PlatformUser;
use App\Models\Plan;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = $this->createPermissions();
        $roles = $this->createRoles($permissions);
        $this->createSuperAdmin();
        $this->createPlans();
        $this->createDefaultSettings();
    }

    private function createPermissions(): array
    {
        $defs = [
            'platform.dashboard.view' => ['Dashboard', 'View dashboard'],
            'platform.businesses.view' => ['View Businesses', 'View businesses'],
            'platform.businesses.create' => ['Create Businesses', 'Create businesses'],
            'platform.businesses.update' => ['Update Businesses', 'Update businesses'],
            'platform.businesses.suspend' => ['Suspend Businesses', 'Suspend businesses'],
            'platform.businesses.delete' => ['Delete Businesses', 'Delete businesses'],
            'platform.users.view' => ['View Users', 'View platform users'],
            'platform.users.create' => ['Create Users', 'Create platform users'],
            'platform.users.update' => ['Update Users', 'Update platform users'],
            'platform.users.delete' => ['Delete Users', 'Delete platform users'],
            'platform.subscriptions.view' => ['View Subscriptions', 'View subscriptions'],
            'platform.subscriptions.create' => ['Create Subscriptions', 'Create subscriptions'],
            'platform.subscriptions.update' => ['Update Subscriptions', 'Update subscriptions'],
            'platform.subscriptions.cancel' => ['Cancel Subscriptions', 'Cancel subscriptions'],
            'platform.plans.view' => ['View Plans', 'View plans'],
            'platform.plans.create' => ['Create Plans', 'Create plans'],
            'platform.plans.update' => ['Update Plans', 'Update plans'],
            'platform.plans.delete' => ['Delete Plans', 'Delete plans'],
            'platform.payments.view' => ['View Payments', 'View payments'],
            'platform.invoices.view' => ['View Invoices', 'View invoices'],
            'platform.audit_logs.view' => ['View Audit Logs', 'View audit logs'],
            'platform.settings.view' => ['View Settings', 'View settings'],
            'platform.settings.update' => ['Update Settings', 'Update settings'],
            'platform.impersonation.use' => ['Use Impersonation', 'Use impersonation'],
        ];

        $created = [];
        foreach ($defs as $slug => [$name, $desc]) {
            $created[$slug] = PlatformPermission::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug,
                    'group' => explode('.', $slug)[1] ?? 'general',
                    'description' => $desc,
                ]
            );
        }
        return $created;
    }

    private function createRoles(array $permissions): array
    {
        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'permissions' => array_keys($permissions),
            ],
            'platform-manager' => [
                'name' => 'Platform Manager',
                'slug' => 'platform-manager',
                'permissions' => array_filter(array_keys($permissions), fn($p) => !in_array($p, ['platform.settings.update', 'platform.users.delete'])),
            ],
            'support-agent' => [
                'name' => 'Support Agent',
                'slug' => 'support-agent',
                'permissions' => ['platform.dashboard.view', 'platform.businesses.view', 'platform.users.view', 'platform.subscriptions.view', 'platform.audit_logs.view', 'platform.impersonation.use'],
            ],
            'billing-manager' => [
                'name' => 'Billing Manager',
                'slug' => 'billing-manager',
                'permissions' => ['platform.dashboard.view', 'platform.subscriptions.view', 'platform.subscriptions.create', 'platform.subscriptions.update', 'platform.plans.view', 'platform.payments.view', 'platform.invoices.view'],
            ],
            'analyst' => [
                'name' => 'Analyst',
                'slug' => 'analyst',
                'permissions' => ['platform.dashboard.view', 'platform.businesses.view', 'platform.subscriptions.view', 'platform.audit_logs.view'],
            ],
        ];

        $created = [];
        foreach ($roles as $slug => $data) {
            $role = PlatformRole::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['name'] . ' role',
                    'is_default' => $slug === 'support-agent',
                ]
            );

            $permIds = collect($data['permissions'])->map(fn($p) => $permissions[$p]->id)->toArray();
            $role->permissions()->detach();
            foreach ($permIds as $permId) {
                \DB::table('platform_role_permissions')->insert([
                    'id' => Str::uuid()->toString(),
                    'role_id' => $role->id,
                    'permission_id' => $permId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $created[$slug] = $role;
        }
        return $created;
    }

    private function createSuperAdmin(): void
    {
        PlatformUser::updateOrCreate(
            ['email' => 'admin@halis.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_super_admin' => true,
            ]
        );
    }

    private function createPlans(): void
    {
        $plans = [
            ['name' => 'Starter', 'slug' => 'starter', 'price_monthly' => 0, 'price_yearly' => 0, 'max_users' => 3, 'max_branches' => 1, 'max_products' => 50, 'max_transactions' => 500, 'trial_days' => 0, 'is_default' => true],
            ['name' => 'Basic', 'slug' => 'basic', 'price_monthly' => 150000, 'price_yearly' => 1500000, 'max_users' => 5, 'max_branches' => 2, 'max_products' => 500, 'max_transactions' => 5000, 'trial_days' => 14, 'loyalty_enabled' => true, 'multi_branch_enabled' => true],
            ['name' => 'Professional', 'slug' => 'professional', 'price_monthly' => 500000, 'price_yearly' => 5000000, 'max_users' => 25, 'max_branches' => 10, 'max_products' => 5000, 'max_transactions' => 50000, 'trial_days' => 14, 'online_store_enabled' => true, 'advanced_reports_enabled' => true, 'loyalty_enabled' => true, 'wallet_enabled' => true, 'multi_branch_enabled' => true, 'multi_unit_enabled' => true],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'price_monthly' => 1500000, 'price_yearly' => 15000000, 'max_users' => 100, 'max_branches' => 50, 'max_products' => 50000, 'max_transactions' => 999999, 'trial_days' => 30, 'online_store_enabled' => true, 'custom_domain_enabled' => true, 'advanced_reports_enabled' => true, 'cloud_backup_enabled' => true, 'api_access_enabled' => true, 'efris_enabled' => true, 'loyalty_enabled' => true, 'wallet_enabled' => true, 'multi_branch_enabled' => true, 'multi_unit_enabled' => true],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

    private function createDefaultSettings(): void
    {
        $settings = [
            ['key' => 'platform_name', 'value' => 'HALIS SaaS', 'type' => 'text', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@halis.com', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'UGX', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_timezone', 'value' => 'Africa/Kampala', 'type' => 'text', 'group' => 'general'],
            ['key' => 'default_trial_days', 'value' => '14', 'type' => 'integer', 'group' => 'subscription'],
            ['key' => 'default_grace_period_days', 'value' => '7', 'type' => 'integer', 'group' => 'subscription'],
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],

            // Email / SMTP settings
            ['key' => 'mail_mailer', 'value' => 'log', 'type' => 'select', 'group' => 'email',
             'options' => json_encode(['log' => 'Log (Development)', 'smtp' => 'SMTP', 'ses' => 'Amazon SES', 'postmark' => 'Postmark', 'resend' => 'Resend'])],
            ['key' => 'mail_host', 'value' => '', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'password', 'group' => 'email'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'select', 'group' => 'email',
             'options' => json_encode(['tls' => 'TLS', 'ssl' => 'SSL', 'null' => 'None'])],
            ['key' => 'mail_from_address', 'value' => 'no-reply@halis.com', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_from_name', 'value' => 'HALIS', 'type' => 'text', 'group' => 'email'],
            ['key' => 'mail_reply_to_address', 'value' => 'support@halis.com', 'type' => 'text', 'group' => 'email'],
        ];

        foreach ($settings as $s) {
            PlatformSetting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
