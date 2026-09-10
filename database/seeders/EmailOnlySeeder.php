<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailOnlySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedEmailPermissions();
        $this->seedEmailTemplates();
    }

    private function seedEmailPermissions(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('roles')) {
            return;
        }

        $emailPerms = [
            'can_view_email' => true,
            'can_compose_email' => true,
            'can_send_email' => true,
            'can_delete_email' => true,
            'can_manage_email_accounts' => true,
            'can_manage_email_labels' => true,
            'can_manage_email_templates' => true,
            'can_sync_email' => true,
        ];

        DB::table('roles')->where('name', 'Admin')->update($emailPerms);

        DB::table('roles')->where('name', 'Cashier')->update([
            'can_view_email' => true,
            'can_compose_email' => true,
            'can_send_email' => true,
            'can_delete_email' => false,
            'can_manage_email_accounts' => false,
            'can_manage_email_labels' => true,
            'can_manage_email_templates' => false,
            'can_sync_email' => false,
        ]);

        $this->command->info('Email permissions seeded.');
    }

    private function seedEmailTemplates(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('email_templates')) {
            return;
        }

        $tenants = DB::table('tenants')->select('id')->get();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $adminUser = DB::table('users')->where('tenant_id', $tenantId)->first();
            $userId = $adminUser?->id ?? DB::table('users')->first()?->id;

            $business = DB::table('businesses')
                ->join('tenants', 'tenants.business_id', '=', 'businesses.id')
                ->where('tenants.id', $tenantId)
                ->first();

            $companyName = $business->name ?? 'Your Business';

            $templates = [
                [
                    'name' => 'Invoice Email',
                    'subject' => "Invoice {{invoice_number}} from {$companyName}",
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px;"><p>Dear {{customer_name}},</p><p>Please find attached your invoice <strong>{{invoice_number}}</strong> for <strong>{{total_amount}} {{currency}}</strong>.</p><p>Thank you for your business.</p><p><strong>' . $companyName . '</strong></p></div>',
                    'category' => 'invoice',
                ],
                [
                    'name' => 'Receipt Email',
                    'subject' => "Receipt {{receipt_number}} from {$companyName}",
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px;"><p>Dear {{customer_name}},</p><p>Thank you for your purchase. Your receipt number is <strong>{{receipt_number}}</strong>.</p><p>Total: <strong>{{total_amount}} {{currency}}</strong></p><p>Paid: <strong>{{amount_paid}} {{currency}}</strong></p><p><strong>' . $companyName . '</strong></p></div>',
                    'category' => 'receipt',
                ],
                [
                    'name' => 'Quotation Email',
                    'subject' => "Quotation {{quote_number}} from {$companyName}",
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px;"><p>Dear {{customer_name}},</p><p>Please find attached your quotation <strong>{{quote_number}}</strong> for <strong>{{total_amount}} {{currency}}</strong>.</p><p>This quotation is valid until <strong>{{valid_until}}</strong>.</p><p><strong>' . $companyName . '</strong></p></div>',
                    'category' => 'quotation',
                ],
                [
                    'name' => 'Payment Reminder',
                    'subject' => "Payment Reminder - {{receipt_number}} - {$companyName}",
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px;"><p>Dear {{customer_name}},</p><p>This is a friendly reminder that your payment of <strong>{{balance_due}} {{currency}}</strong> for invoice <strong>{{receipt_number}}</strong> is overdue.</p><p>Please arrange payment at your earliest convenience.</p><p><strong>' . $companyName . '</strong></p></div>',
                    'category' => 'payment',
                ],
                [
                    'name' => 'Account Statement',
                    'subject' => "Account Statement - {$companyName}",
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px;"><p>Dear {{customer_name}},</p><p>Please find below your account statement.</p><p>Outstanding Balance: <strong>{{balance}} {{currency}}</strong></p><p><strong>' . $companyName . '</strong></p></div>',
                    'category' => 'statement',
                ],
            ];

            foreach ($templates as $template) {
                DB::table('email_templates')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'name' => $template['name']],
                    array_merge([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'created_by' => $userId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $template)
                );
            }

            $this->command->info("Email templates seeded for tenant: {$tenantId}");
        }
    }
}
