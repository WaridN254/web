<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('platform_user_id')->nullable();
            $table->uuid('tenant_id')->nullable();
            $table->string('action');
            $table->string('resource_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('platform_user_id')->references('id')->on('platform_users')->onDelete('set null');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');

            $table->index('platform_user_id');
            $table->index('tenant_id');
            $table->index('action');
            $table->index('resource_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};
