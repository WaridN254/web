<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->uuid('branch_id')->nullable()->after('tenant_id');
            $table->index('branch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->uuid('serial_id')->nullable()->after('product_id');
            $table->string('serial_number')->nullable()->after('serial_id');
            $table->index('serial_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['serial_id']);
            $table->dropColumn(['serial_id', 'serial_number']);
        });
    }
};
