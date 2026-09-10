<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_order_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('online_order_id');
            $table->string('product_id');
            $table->string('variant_id')->nullable();
            $table->string('product_name');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('sale_unit_id')->nullable();
            $table->string('sale_unit_name')->nullable();
            $table->decimal('unit_conversion_to_base', 10, 4)->default(1);
            $table->decimal('base_quantity', 12, 3)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'online_order_id']);
            $table->index(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_items');
    }
};
