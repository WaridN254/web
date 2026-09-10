<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->text('product_id');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();

            // Pricing: 'inherited' or 'custom'
            $table->string('selling_price_mode')->default('inherited');
            $table->decimal('custom_selling_price', 12, 2)->nullable();
            $table->string('cost_price_mode')->default('inherited');
            $table->decimal('custom_cost_price', 12, 2)->nullable();

            // Stock (non-serialized variants)
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);

            // Serialization
            $table->boolean('track_serial_numbers')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
            $table->index(['product_id', 'is_active']);
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('variant_id');
            $table->uuid('attribute_id');
            $table->uuid('attribute_value_id');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->onDelete('cascade');
            $table->foreign('attribute_value_id')->references('id')->on('variant_attribute_values')->onDelete('cascade');
            $table->unique(['variant_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
    }
};
