<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_user_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('permission_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('platform_users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('platform_permissions')->onDelete('cascade');
            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_user_permissions');
    }
};
