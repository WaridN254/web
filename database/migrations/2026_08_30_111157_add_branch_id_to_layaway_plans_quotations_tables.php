<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layaway_plans', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });

        Schema::table('cash_drawer_logs', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('layaway_plans', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('cash_drawer_logs', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
