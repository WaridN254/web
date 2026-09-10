<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('invoice_id')->nullable();
            $table->uuid('subscription_id')->nullable();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UGX');
            $table->string('status')->default('pending');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->string('external_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('set null');

            $table->index('tenant_id');
            $table->index('status');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
