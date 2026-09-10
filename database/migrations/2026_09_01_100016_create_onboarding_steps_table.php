<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('current_step')->default('business');
            $table->boolean('business_completed')->default(false);
            $table->boolean('branch_completed')->default(false);
            $table->boolean('pos_completed')->default(false);
            $table->boolean('tax_completed')->default(false);
            $table->boolean('receipt_completed')->default(false);
            $table->boolean('payment_methods_completed')->default(false);
            $table->boolean('products_completed')->default(false);
            $table->boolean('opening_stock_completed')->default(false);
            $table->boolean('team_completed')->default(false);
            $table->boolean('hardware_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_steps');
    }
};
