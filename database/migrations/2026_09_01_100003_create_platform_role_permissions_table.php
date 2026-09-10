<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_role_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('platform_roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('platform_permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_role_permissions');
    }
};
