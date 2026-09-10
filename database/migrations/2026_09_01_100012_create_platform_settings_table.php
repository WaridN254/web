<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();

            $table->index('group');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
