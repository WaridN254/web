<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transactions (sales)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['branch_id', 'created_at']);
        });

        // Purchase orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
            $table->index(['tenant_id', 'branch_id']);
        });

        // Cash movements (expenses)
        Schema::table('cash_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_movements', 'branch_id')) {
                $table->string('branch_id')->nullable()->after('tenant_id');
                $table->index(['tenant_id', 'branch_id']);
            }
        });

        // POS sessions
        Schema::table('sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('sessions', 'branch_id')) {
                $table->string('branch_id')->nullable()->after('tenant_id');
                $table->index(['tenant_id', 'branch_id']);
            }
        });

        // Stock movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['branch_id', 'product_id']);
        });

        // Stock audits
        Schema::table('stock_audits', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->after('tenant_id');
            $table->index(['tenant_id', 'branch_id']);
        });

        // Customer ledger
        Schema::table('customer_ledger', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_ledger', 'branch_id')) {
                $table->string('branch_id')->nullable()->after('tenant_id');
                $table->index(['tenant_id', 'branch_id']);
            }
        });

        // Users - add default_branch_id
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'default_branch_id')) {
                $table->string('default_branch_id')->nullable()->after('tenant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropIndex(['branch_id', 'created_at']);
            $table->dropColumn('branch_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropIndex(['branch_id', 'product_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('stock_audits', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('customer_ledger', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_branch_id');
        });
    }
};
