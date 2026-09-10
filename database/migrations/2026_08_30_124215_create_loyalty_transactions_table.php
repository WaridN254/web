<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('loyalty_account_id');
            $table->string('customer_id');
            $table->string('branch_id')->nullable();
            $table->string('type'); // earn, redeem, adjustment, expiry
            $table->integer('points');
            $table->string('reference_type')->nullable(); // transaction, manual
            $table->string('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'loyalty_account_id']);
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
