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
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('tenant_id');
            });
        }

        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('tenant_id');
            });
        }

        if (Schema::hasTable('payment_methods') && ! Schema::hasColumn('payment_methods', 'sort_order')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'sort_order')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
