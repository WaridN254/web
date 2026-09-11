<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('email_recipients', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        DB::statement('
            UPDATE email_recipients
            SET tenant_id = emails.tenant_id
            FROM emails
            WHERE email_recipients.email_id = emails.id
            AND email_recipients.tenant_id IS NULL
        ');

        Schema::table('email_recipients', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable(false)->change();
            $table->index(['tenant_id', 'email_id']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('email_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('email_recipients', 'tenant_id')) {
                $table->dropIndex(['tenant_id', 'email_id']);
                $table->dropIndex(['tenant_id', 'type']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
