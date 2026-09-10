<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('transfer_number');
            $table->string('from_branch_id');
            $table->string('to_branch_id');
            $table->string('requested_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('received_by')->nullable();
            $table->string('status')->default('draft'); // draft, pending, approved, in_transit, received, cancelled, rejected
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'from_branch_id']);
            $table->index(['tenant_id', 'to_branch_id']);
            $table->unique(['tenant_id', 'transfer_number']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('transfer_id');
            $table->string('product_id');
            $table->string('variant_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('received_quantity', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transfer_id']);
            $table->index(['tenant_id', 'product_id']);
        });

        Schema::create('stock_transfer_serials', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('transfer_id');
            $table->string('serial_id');
            $table->string('status')->default('pending'); // pending, transferred, received
            $table->timestamps();

            $table->index(['transfer_id']);
            $table->index(['tenant_id', 'serial_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_serials');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
