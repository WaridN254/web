<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });

        Schema::table('stock_audit_items', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('stock_audit_items', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
