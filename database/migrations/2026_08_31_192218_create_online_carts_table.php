<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_carts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('customer_id')->nullable();
            $table->string('session_id')->nullable();
            $table->json('items')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_carts');
    }
};
