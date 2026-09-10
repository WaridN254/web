<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $permissions = [
            'can_access_admin_dashboard',
            'can_access_sales_dashboard',
            'can_access_dashboard_2',
        ];

        foreach ($permissions as $permission) {
            if (! Schema::hasColumn('roles', $permission)) {
                Schema::table('roles', function (Blueprint $table) use ($permission) {
                    $table->boolean($permission)->default(false);
                });
            }
        }

        // Grant full dashboard access to existing all-access roles.
        DB::table('roles')
            ->whereIn('name', ['owner', 'admin', 'administrator', 'super_admin'])
            ->update([
                'can_access_admin_dashboard' => true,
                'can_access_sales_dashboard' => true,
                'can_access_dashboard_2' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach (['can_access_admin_dashboard', 'can_access_sales_dashboard', 'can_access_dashboard_2'] as $permission) {
            if (Schema::hasColumn('roles', $permission)) {
                Schema::table('roles', function (Blueprint $table) use ($permission) {
                    $table->dropColumn($permission);
                });
            }
        }
    }
};