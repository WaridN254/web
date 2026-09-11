<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_branches', function (Blueprint $table) {
            if (!Schema::hasColumn('user_branches', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        DB::statement('
            UPDATE user_branches
            SET tenant_id = branches.tenant_id
            FROM branches
            WHERE user_branches.branch_id = branches.id
            AND user_branches.tenant_id IS NULL
        ');

        Schema::table('user_branches', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable(false)->change();
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_branches', function (Blueprint $table) {
            if (Schema::hasColumn('user_branches', 'tenant_id')) {
                $table->dropIndex(['tenant_id', 'user_id']);
                $table->dropIndex(['tenant_id', 'branch_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
