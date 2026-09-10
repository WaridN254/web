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

        DB::table('user_branches')
            ->join('branches', 'user_branches.branch_id', '=', 'branches.id')
            ->whereNull('user_branches.tenant_id')
            ->update(['user_branches.tenant_id' => DB::raw('branches.tenant_id')]);

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
