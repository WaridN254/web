<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('email_address');
            $table->string('display_name')->nullable();
            $table->string('provider')->default('custom');
            $table->string('authentication_type')->default('password');
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->default('tls');
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('imap_host')->nullable();
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl');
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable();
            $table->boolean('sync_enabled')->default(true);
            $table->integer('sync_interval_minutes')->default(5);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('connection_status')->default('disconnected');
            $table->text('last_connection_error')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'email_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
