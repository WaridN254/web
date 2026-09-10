<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('plan_id');
            $table->string('status')->default('trialing');
            $table->string('billing_cycle')->default('monthly');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('UGX');
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('external_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans');

            $table->index('tenant_id');
            $table->index('status');
            $table->index('plan_id');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
