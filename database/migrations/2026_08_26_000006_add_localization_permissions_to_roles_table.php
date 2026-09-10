<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('can_view_localization_settings')->default(false);
            $table->boolean('can_manage_localization_settings')->default(false);
            $table->boolean('can_manage_languages')->default(false);
            $table->boolean('can_manage_translations')->default(false);
            $table->boolean('can_manage_currencies')->default(false);
            $table->boolean('can_manage_exchange_rates')->default(false);
        });

        // Owner gets all localization permissions
        DB::table('roles')
            ->where('name', 'owner')
            ->update([
                'can_view_localization_settings' => true,
                'can_manage_localization_settings' => true,
                'can_manage_languages' => true,
                'can_manage_translations' => true,
                'can_manage_currencies' => true,
                'can_manage_exchange_rates' => true,
            ]);

        // Admin gets view + manage
        DB::table('roles')
            ->where('name', 'Admin')
            ->update([
                'can_view_localization_settings' => true,
                'can_manage_localization_settings' => true,
                'can_manage_languages' => true,
                'can_manage_translations' => true,
                'can_manage_currencies' => true,
                'can_manage_exchange_rates' => true,
            ]);

        // Cashier gets view only
        DB::table('roles')
            ->where('name', 'Cashier')
            ->update([
                'can_view_localization_settings' => false,
                'can_manage_localization_settings' => false,
                'can_manage_languages' => false,
                'can_manage_translations' => false,
                'can_manage_currencies' => false,
                'can_manage_exchange_rates' => false,
            ]);

        DB::table('roles')
            ->where('name', 'cashier')
            ->update([
                'can_view_localization_settings' => false,
                'can_manage_localization_settings' => false,
                'can_manage_languages' => false,
                'can_manage_translations' => false,
                'can_manage_currencies' => false,
                'can_manage_exchange_rates' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'can_view_localization_settings',
                'can_manage_localization_settings',
                'can_manage_languages',
                'can_manage_translations',
                'can_manage_currencies',
                'can_manage_exchange_rates',
            ]);
        });
    }
};
