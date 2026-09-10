<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'can_view_email' => 'boolean',
            'can_compose_email' => 'boolean',
            'can_send_email' => 'boolean',
            'can_delete_email' => 'boolean',
            'can_manage_email_accounts' => 'boolean',
            'can_manage_email_labels' => 'boolean',
            'can_manage_email_templates' => 'boolean',
            'can_sync_email' => 'boolean',
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('roles', $column)) {
                Schema::table('roles', function ($table) use ($column) {
                    $table->boolean($column)->default(false);
                });
            }
        }

        DB::table('roles')->where('name', 'owner')->update([
            'can_view_email' => true,
            'can_compose_email' => true,
            'can_send_email' => true,
            'can_delete_email' => true,
            'can_manage_email_accounts' => true,
            'can_manage_email_labels' => true,
            'can_manage_email_templates' => true,
            'can_sync_email' => true,
        ]);

        DB::table('roles')->where('name', 'manager')->update([
            'can_view_email' => true,
            'can_compose_email' => true,
            'can_send_email' => true,
            'can_delete_email' => false,
            'can_manage_email_accounts' => false,
            'can_manage_email_labels' => true,
            'can_manage_email_templates' => true,
            'can_sync_email' => true,
        ]);
    }

    public function down(): void
    {
        $columns = [
            'can_view_email', 'can_compose_email', 'can_send_email',
            'can_delete_email', 'can_manage_email_accounts', 'can_manage_email_labels',
            'can_manage_email_templates', 'can_sync_email',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('roles', $column)) {
                Schema::table('roles', function ($table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
