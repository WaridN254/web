<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'default_language')) {
                $table->string('default_language', 10)->default('en')->after('country_code');
            }
            if (!Schema::hasColumn('tenants', 'default_locale')) {
                $table->string('default_locale', 20)->default('en')->after('default_language');
            }
            if (!Schema::hasColumn('tenants', 'default_currency')) {
                $table->string('default_currency', 10)->default('USD')->after('default_locale');
            }
            if (!Schema::hasColumn('tenants', 'date_format')) {
                $table->string('date_format', 20)->default('d/m/Y')->after('timezone');
            }
            if (!Schema::hasColumn('tenants', 'time_format')) {
                $table->string('time_format', 10)->default('24')->after('date_format');
            }
            if (!Schema::hasColumn('tenants', 'number_format')) {
                $table->string('number_format', 20)->default('1,234.56')->after('time_format');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['default_language', 'default_locale', 'default_currency', 'date_format', 'time_format', 'number_format'] as $col) {
                if (Schema::hasColumn('tenants', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
