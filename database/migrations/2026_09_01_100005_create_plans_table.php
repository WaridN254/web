<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('price_monthly')->default('0');
            $table->string('price_yearly')->default('0');
            $table->string('currency', 3)->default('UGX');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);

            // Limits
            $table->integer('max_users')->default(5);
            $table->integer('max_branches')->default(1);
            $table->integer('max_products')->default(100);
            $table->integer('max_transactions')->default(1000);
            $table->string('max_storage')->default('100MB');
            $table->integer('trial_days')->default(14);

            // Feature flags
            $table->boolean('online_store_enabled')->default(false);
            $table->boolean('custom_domain_enabled')->default(false);
            $table->boolean('advanced_reports_enabled')->default(false);
            $table->boolean('cloud_backup_enabled')->default(false);
            $table->boolean('api_access_enabled')->default(false);
            $table->boolean('multi_unit_enabled')->default(false);
            $table->boolean('efris_enabled')->default(false);
            $table->boolean('loyalty_enabled')->default(false);
            $table->boolean('wallet_enabled')->default(false);
            $table->boolean('multi_branch_enabled')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
