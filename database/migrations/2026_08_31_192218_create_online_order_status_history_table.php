<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_order_status_history', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('online_order_id');
            $table->string('status');
            $table->string('previous_status')->nullable();
            $table->text('notes')->nullable();
            $table->string('changed_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'online_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_status_history');
    }
};
