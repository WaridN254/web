<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id', 36)->nullable();
            $table->string('base_currency', 10);
            $table->string('target_currency', 10);
            $table->decimal('rate', 20, 8);
            $table->string('provider')->default('manual');
            $table->timestamp('effective_at');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['base_currency', 'target_currency']);
            $table->index('effective_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
