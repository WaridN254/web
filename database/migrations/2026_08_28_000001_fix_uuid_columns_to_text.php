<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'user_branches' => ['user_id', 'branch_id'],
            'branch_stocks' => ['tenant_id', 'branch_id', 'product_id', 'variant_id'],
            'stock_transfers' => ['tenant_id', 'from_branch_id', 'to_branch_id', 'requested_by', 'approved_by', 'received_by'],
            'stock_transfer_items' => ['tenant_id', 'transfer_id', 'product_id', 'variant_id'],
            'stock_transfer_serials' => ['tenant_id', 'transfer_id', 'serial_id'],
        ];

        foreach ($tables as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE text USING {$column}::text");
            }
            DB::statement("ALTER TABLE {$table} ALTER COLUMN id TYPE text USING id::text");
        }

        $branchIdColumns = [
            'transactions' => ['branch_id'],
            'purchase_orders' => ['branch_id'],
            'cash_movements' => ['branch_id'],
            'sessions' => ['branch_id'],
            'stock_movements' => ['branch_id'],
            'stock_audits' => ['branch_id'],
            'customer_ledger' => ['branch_id'],
            'users' => ['default_branch_id'],
        ];

        foreach ($branchIdColumns as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE text USING {$column}::text");
            }
        }
    }

    public function down(): void
    {
        //
    }
};
