<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_store_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->boolean('is_enabled')->default(false);
            $table->string('store_name')->nullable();
            $table->text('store_description')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('fulfillment_strategy')->default('branch_with_stock');
            $table->boolean('allow_pickup')->default(true);
            $table->boolean('allow_delivery')->default(false);
            $table->json('pickup_branches')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('free_delivery_threshold', 10, 2)->nullable();
            $table->string('currency_code', 3)->default('UGX');
            $table->boolean('tax_included')->default(false);
            $table->string('custom_domain')->nullable();
            $table->boolean('allow_guest_checkout')->default(true);
            $table->boolean('require_phone')->default(true);
            $table->boolean('require_address')->default(false);
            $table->boolean('show_stock_levels')->default(false);
            $table->boolean('allow_branch_selection')->default(true);
            $table->json('business_hours')->nullable();
            $table->json('contact_info')->nullable();
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_store_settings');
    }
};
