<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            if (! Schema::hasColumn('transactions', 'cashier_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->uuid('cashier_id')->nullable()->after('user_id');
                });
            }

            if (! Schema::hasColumn('transactions', 'payment_method_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->uuid('payment_method_id')->nullable()->after('total_amount');
                });
            }

            if (! Schema::hasColumn('transactions', 'settlement_status')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('settlement_status')->nullable()->after('status');
                });
            }

            if (! Schema::hasColumn('transactions', 'transaction_type')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('transaction_type')->nullable()->after('document_type');
                });
            }
        }

        if (Schema::hasTable('transaction_items')) {
            if (! Schema::hasColumn('transaction_items', 'discount_applied')) {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->decimal('discount_applied', 12, 2)->default(0)->after('discount');
                });
            }

            if (! Schema::hasColumn('transaction_items', 'is_deleted')) {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->boolean('is_deleted')->default(false)->after('tenant_id');
                });
            }

            if (! Schema::hasColumn('transaction_items', 'sort_order')) {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->integer('sort_order')->default(0)->after('tenant_id');
                });
            }
        }

        if (Schema::hasTable('transaction_payments')) {
            if (! Schema::hasColumn('transaction_payments', 'payment_method_id')) {
                Schema::table('transaction_payments', function (Blueprint $table) {
                    $table->uuid('payment_method_id')->nullable()->after('transaction_id');
                });
            }

            if (! Schema::hasColumn('transaction_payments', 'reference_number')) {
                Schema::table('transaction_payments', function (Blueprint $table) {
                    $table->string('reference_number')->nullable()->after('amount_paid');
                });
            }

            if (! Schema::hasColumn('transaction_payments', 'is_deleted')) {
                Schema::table('transaction_payments', function (Blueprint $table) {
                    $table->boolean('is_deleted')->default(false)->after('tenant_id');
                });
            }

            if (! Schema::hasColumn('transaction_payments', 'sort_order')) {
                Schema::table('transaction_payments', function (Blueprint $table) {
                    $table->integer('sort_order')->default(0)->after('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            foreach (['cashier_id', 'payment_method_id', 'settlement_status', 'transaction_type'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    Schema::table('transactions', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('transaction_items')) {
            foreach (['discount_applied', 'is_deleted', 'sort_order'] as $column) {
                if (Schema::hasColumn('transaction_items', $column)) {
                    Schema::table('transaction_items', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('transaction_payments')) {
            foreach (['payment_method_id', 'reference_number', 'is_deleted', 'sort_order'] as $column) {
                if (Schema::hasColumn('transaction_payments', $column)) {
                    Schema::table('transaction_payments', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
