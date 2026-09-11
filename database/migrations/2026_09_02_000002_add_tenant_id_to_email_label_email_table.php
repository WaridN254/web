<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_label_email', function (Blueprint $table) {
            if (!Schema::hasColumn('email_label_email', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        DB::statement('
            UPDATE email_label_email
            SET tenant_id = emails.tenant_id
            FROM emails
            WHERE email_label_email.email_id = emails.id
            AND email_label_email.tenant_id IS NULL
        ');

        Schema::table('email_label_email', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable(false)->change();
            $table->index(['tenant_id', 'email_id']);
            $table->index(['tenant_id', 'email_label_id']);
        });
    }

    public function down(): void
    {
        Schema::table('email_label_email', function (Blueprint $table) {
            if (Schema::hasColumn('email_label_email', 'tenant_id')) {
                $table->dropIndex(['tenant_id', 'email_id']);
                $table->dropIndex(['tenant_id', 'email_label_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
