<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('attribute_id');
            $table->string('value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->onDelete('cascade');
            $table->unique(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('variant_attributes');
    }
};
