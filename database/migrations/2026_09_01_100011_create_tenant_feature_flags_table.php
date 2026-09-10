<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_feature_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('feature_flag_id');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('feature_flag_id')->references('id')->on('feature_flags')->onDelete('cascade');
            $table->unique(['tenant_id', 'feature_flag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_flags');
    }
};
