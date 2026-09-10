<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('track_serial_numbers');
        });

        Schema::table('product_serials', function (Blueprint $table) {
            $table->uuid('variant_id')->nullable()->after('product_id');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->uuid('variant_id')->nullable()->after('product_id');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'product_id')) {
                $table->uuid('variant_id')->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_variants');
        });

        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'variant_id')) {
                $table->dropColumn('variant_id');
            }
        });
    }
};
