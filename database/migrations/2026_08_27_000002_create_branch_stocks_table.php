<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('branch_id');
            $table->string('product_id');
            $table->string('variant_id')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0);
            $table->decimal('minimum_stock', 15, 4)->nullable();
            $table->decimal('maximum_stock', 15, 4)->nullable();
            $table->decimal('average_cost', 15, 4)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
            $table->index(['branch_id', 'product_id']);
            $table->index(['tenant_id', 'product_id']);
            $table->unique(['branch_id', 'product_id', 'variant_id'], 'branch_stock_product_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};
