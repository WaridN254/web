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

        DB::table('email_label_email')
            ->join('emails', 'email_label_email.email_id', '=', 'emails.id')
            ->whereNull('email_label_email.tenant_id')
            ->update(['email_label_email.tenant_id' => DB::raw('emails.tenant_id')]);

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
