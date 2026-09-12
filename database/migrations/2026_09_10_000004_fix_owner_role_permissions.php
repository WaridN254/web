<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $allPermissions = [
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
        ];

        DB::table('roles')->where('name', 'owner')->update(
            array_merge($allPermissions, ['updated_at' => now()])
        );
    }

    public function down(): void
    {
        // No-op: permissions can be revoked manually
    }
};
