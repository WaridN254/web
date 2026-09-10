<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->string('symbol_first')->default('before');
            $table->integer('minor_unit')->default(2);
            $table->integer('decimal_places')->default(2);
            $table->string('decimal_separator', 5)->default('.');
            $table->string('thousands_separator', 5)->default(',');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
