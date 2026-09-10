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
        if (Schema::hasTable('transaction_items') && ! Schema::hasColumn('transaction_items', 'is_deleted')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('tenant_id');
            });
        }

        if (Schema::hasTable('transaction_items') && ! Schema::hasColumn('transaction_items', 'sort_order')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('tenant_id');
            });
        }

        if (Schema::hasTable('transaction_payments') && ! Schema::hasColumn('transaction_payments', 'is_deleted')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transaction_items')) {
            if (Schema::hasColumn('transaction_items', 'is_deleted')) {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->dropColumn('is_deleted');
                });
            }

            if (Schema::hasColumn('transaction_items', 'sort_order')) {
                Schema::table('transaction_items', function (Blueprint $table) {
                    $table->dropColumn('sort_order');
                });
            }
        }

        if (Schema::hasTable('transaction_payments') && Schema::hasColumn('transaction_payments', 'is_deleted')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
