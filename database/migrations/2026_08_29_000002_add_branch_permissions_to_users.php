<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_manage_branches')->default(false)->after('can_view_all_branches');
            $table->boolean('can_manage_stock_transfers')->default(false)->after('can_manage_branches');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_manage_branches', 'can_manage_stock_transfers']);
        });
    }
};
