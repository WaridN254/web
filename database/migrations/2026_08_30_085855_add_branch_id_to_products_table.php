<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add branch_id column if it doesn't exist yet
        if (!Schema::hasColumn('products', 'branch_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('branch_id')->nullable()->after('tenant_id');
                $table->index(['tenant_id', 'branch_id']);
            });
        }

        // Backfill branch_id for products that don't have one yet
        DB::statement("
            UPDATE products 
            SET branch_id = COALESCE(
                (SELECT branch_id FROM branch_stocks WHERE branch_stocks.product_id::text = products.id::text ORDER BY quantity DESC LIMIT 1),
                (SELECT id::text FROM branches WHERE branches.tenant_id::text = products.tenant_id::text AND branches.is_active = true ORDER BY created_at ASC LIMIT 1)
            )
            WHERE branch_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
