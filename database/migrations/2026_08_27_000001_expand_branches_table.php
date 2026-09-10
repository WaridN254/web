<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('timezone')->nullable()->after('country');
            $table->string('manager_id')->nullable()->after('timezone');
            $table->boolean('is_default')->default(false)->after('manager_id');
            $table->string('currency_code', 3)->nullable()->after('is_default');

            $table->index('code');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'email', 'city', 'country',
                'timezone', 'manager_id', 'is_default', 'currency_code',
            ]);
        });
    }
};
