<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('transaction_payments')) {
            return;
        }

        if (! Schema::hasColumn('transaction_payments', 'created_at')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasColumn('transaction_payments', 'updated_at')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transaction_payments')) {
            foreach (['created_at', 'updated_at'] as $column) {
                if (Schema::hasColumn('transaction_payments', $column)) {
                    Schema::table('transaction_payments', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
