<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_wallets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('customer_id');
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_deposited', 12, 2)->default(0);
            $table->decimal('total_used', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wallets');
    }
};
