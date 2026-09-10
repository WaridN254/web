<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = DB::table('tenants')->select('id')->get();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            $templates = [
                [
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'created_by' => DB::table('users')->where('tenant_id', $tenantId)->first()?->id ?? DB::table('users')->first()->id,
                    'name' => 'Invoice Email',
                    'subject' => 'Invoice {{invoice_number}} from {{business_name}}',
                    'body_text' => 'Dear {{customer_name}},\n\nPlease find attached your invoice {{invoice_number}} for {{total_amount}} {{currency}}.\n\nThank you for your business.\n\n{{business_name}}',
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;"><p>Dear {{customer_name}},</p><p>Please find attached your invoice <strong>{{invoice_number}}</strong> for <strong>{{total_amount}} {{currency}}</strong>.</p><p>Thank you for your business.</p><p><strong>{{business_name}}</strong></p></div>',
                    'category' => 'invoice',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'created_by' => DB::table('users')->where('tenant_id', $tenantId)->first()?->id ?? DB::table('users')->first()->id,
                    'name' => 'Receipt Email',
                    'subject' => 'Receipt {{receipt_number}} from {{business_name}}',
                    'body_text' => 'Dear {{customer_name}},\n\nThank you for your purchase. Your receipt number is {{receipt_number}}.\n\nTotal: {{total_amount}} {{currency}}\nPaid: {{amount_paid}} {{currency}}\n\n{{business_name}}',
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;"><p>Dear {{customer_name}},</p><p>Thank you for your purchase. Your receipt number is <strong>{{receipt_number}}</strong>.</p><p>Total: <strong>{{total_amount}} {{currency}}</strong></p><p>Paid: <strong>{{amount_paid}} {{currency}}</strong></p><p><strong>{{business_name}}</strong></p></div>',
                    'category' => 'receipt',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'created_by' => DB::table('users')->where('tenant_id', $tenantId)->first()?->id ?? DB::table('users')->first()->id,
                    'name' => 'Quotation Email',
                    'subject' => 'Quotation {{quote_number}} from {{business_name}}',
                    'body_text' => 'Dear {{customer_name}},\n\nPlease find attached your quotation {{quote_number}} for {{total_amount}} {{currency}}.\n\nThis quotation is valid until {{valid_until}}.\n\n{{business_name}}',
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;"><p>Dear {{customer_name}},</p><p>Please find attached your quotation <strong>{{quote_number}}</strong> for <strong>{{total_amount}} {{currency}}</strong>.</p><p>This quotation is valid until <strong>{{valid_until}}</strong>.</p><p><strong>{{business_name}}</strong></p></div>',
                    'category' => 'quotation',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'created_by' => DB::table('users')->where('tenant_id', $tenantId)->first()?->id ?? DB::table('users')->first()->id,
                    'name' => 'Payment Reminder',
                    'subject' => 'Payment Reminder - {{receipt_number}} - {{business_name}}',
                    'body_text' => 'Dear {{customer_name}},\n\nThis is a friendly reminder that your payment of {{balance_due}} {{currency}} for invoice {{receipt_number}} is overdue.\n\nPlease arrange payment at your earliest convenience.\n\n{{business_name}}',
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;"><p>Dear {{customer_name}},</p><p>This is a friendly reminder that your payment of <strong>{{balance_due}} {{currency}}</strong> for invoice <strong>{{receipt_number}}</strong> is overdue.</p><p>Please arrange payment at your earliest convenience.</p><p><strong>{{business_name}}</strong></p></div>',
                    'category' => 'payment',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'created_by' => DB::table('users')->where('tenant_id', $tenantId)->first()?->id ?? DB::table('users')->first()->id,
                    'name' => 'Account Statement',
                    'subject' => 'Account Statement - {{customer_name}} - {{business_name}}',
                    'body_text' => 'Dear {{customer_name}},\n\nPlease find below your account statement.\n\nOutstanding Balance: {{balance}} {{currency}}\n\n{{business_name}}',
                    'body_html' => '<div style="font-family:sans-serif;max-width:600px;margin:0 auto;"><p>Dear {{customer_name}},</p><p>Please find below your account statement.</p><p>Outstanding Balance: <strong>{{balance}} {{currency}}</strong></p><p><strong>{{business_name}}</strong></p></div>',
                    'category' => 'statement',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($templates as $template) {
                DB::table('email_templates')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'name' => $template['name']],
                    $template
                );
            }
        }
    }
}
