<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'transaction_id')) {
                $table->index('transaction_id');
            }
            if (Schema::hasColumn('transaction_items', 'product_id')) {
                $table->index('product_id');
            }
            if (Schema::hasColumn('transaction_items', 'tenant_id') && Schema::hasColumn('transaction_items', 'product_id')) {
                $table->index(['tenant_id', 'product_id']);
            }
        });

        Schema::table('transaction_payments', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_payments', 'transaction_id')) {
                $table->index('transaction_id');
            }
            if (Schema::hasColumn('transaction_payments', 'tenant_id') && Schema::hasColumn('transaction_payments', 'branch_id')) {
                $table->index(['tenant_id', 'branch_id']);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'barcode')) {
                $table->index('barcode');
            }
            if (Schema::hasColumn('products', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'tenant_id')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('stock_audit_items', function (Blueprint $table) {
            if (Schema::hasColumn('stock_audit_items', 'audit_id')) {
                $table->index('audit_id');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'purchase_id')) {
                $table->index('purchase_id');
            }
            if (Schema::hasColumn('purchase_items', 'product_id')) {
                $table->index('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'transaction_id')) {
                $table->dropIndex(['transaction_id']);
            }
            if (Schema::hasColumn('transaction_items', 'product_id')) {
                $table->dropIndex(['product_id']);
            }
            if (Schema::hasColumn('transaction_items', 'tenant_id') && Schema::hasColumn('transaction_items', 'product_id')) {
                $table->dropIndex(['tenant_id', 'product_id']);
            }
        });

        Schema::table('transaction_payments', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_payments', 'transaction_id')) {
                $table->dropIndex(['transaction_id']);
            }
            if (Schema::hasColumn('transaction_payments', 'tenant_id') && Schema::hasColumn('transaction_payments', 'branch_id')) {
                $table->dropIndex(['tenant_id', 'branch_id']);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'barcode')) {
                $table->dropIndex(['barcode']);
            }
            if (Schema::hasColumn('products', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
            }
        });

        Schema::table('stock_audit_items', function (Blueprint $table) {
            if (Schema::hasColumn('stock_audit_items', 'audit_id')) {
                $table->dropIndex(['audit_id']);
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'purchase_id')) {
                $table->dropIndex(['purchase_id']);
            }
            if (Schema::hasColumn('purchase_items', 'product_id')) {
                $table->dropIndex(['product_id']);
            }
        });
    }
};
