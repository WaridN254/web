<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_store_settings', function (Blueprint $table) {
            $table->string('url_slug')->nullable()->after('store_name');
        });

        $tenantId = DB::table('tenants')->orderBy('created_at')->first()?->id;
        if ($tenantId) {
            DB::table('online_store_settings')
                ->where('tenant_id', $tenantId)
                ->whereNull('url_slug')
                ->update(['url_slug' => 'my-store']);
        }
    }

    public function down(): void
    {
        Schema::table('online_store_settings', function (Blueprint $table) {
            $table->dropColumn('url_slug');
        });
    }
};
