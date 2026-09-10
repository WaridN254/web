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
        if (Schema::hasTable('transactions') && ! Schema::hasColumn('transactions', 'due_date')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('due_date')->nullable()->after('transaction_date');
            });
        }

        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', 'can_override_credit_limit')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('can_override_credit_limit')->default(false);
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->whereIn('name', ['owner', 'admin', 'administrator', 'super_admin'])
                ->update(['can_override_credit_limit' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'due_date')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('due_date');
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'can_override_credit_limit')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('can_override_credit_limit');
            });
        }
    }
};
