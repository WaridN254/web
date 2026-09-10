<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->text('user_id');
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

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'email_address']);
        });

        Schema::create('email_threads', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('email_account_id');
            $table->string('subject');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->integer('message_count')->default(0);
            $table->string('folder')->default('inbox');
            $table->timestamps();

            $table->index(['tenant_id', 'email_account_id']);
            $table->index(['tenant_id', 'folder']);
            $table->index(['tenant_id', 'last_message_at']);
            $table->index(['tenant_id', 'is_read']);
            $table->index(['tenant_id', 'is_starred']);
        });

        Schema::create('emails', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('email_account_id');
            $table->uuid('thread_id')->nullable();
            $table->string('external_message_id')->nullable();
            $table->string('external_uid')->nullable();
            $table->string('in_reply_to')->nullable();
            $table->text('references')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_address');
            $table->string('subject');
            $table->text('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('folder')->default('inbox');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->boolean('has_attachments')->default(false);
            $table->integer('attachment_count')->default(0);
            $table->timestamp('received_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email_account_id']);
            $table->index(['tenant_id', 'thread_id']);
            $table->index(['tenant_id', 'folder']);
            $table->index(['tenant_id', 'external_message_id']);
            $table->index(['tenant_id', 'received_at']);
            $table->index(['tenant_id', 'is_read']);
            $table->index(['tenant_id', 'is_starred']);
            $table->index(['tenant_id', 'from_address']);
        });

        Schema::create('email_recipients', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id');
            $table->string('type')->default('to');
            $table->string('name')->nullable();
            $table->string('email_address');
            $table->timestamps();

            $table->index(['email_id', 'type']);
        });

        Schema::create('email_attachments', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('email_id');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->string('content_id')->nullable();
            $table->boolean('is_inline')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'email_id']);
        });

        Schema::create('email_labels', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('email_label_email', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id');
            $table->uuid('email_label_id');
            $table->timestamps();

            $table->unique(['email_id', 'email_label_id']);
        });

        Schema::create('email_templates', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->text('created_by');
            $table->string('name');
            $table->string('subject');
            $table->text('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_label_email');
        Schema::dropIfExists('email_labels');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('email_recipients');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('email_threads');
        Schema::dropIfExists('email_accounts');
    }
};
