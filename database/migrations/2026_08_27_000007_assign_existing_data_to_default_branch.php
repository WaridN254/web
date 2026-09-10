<?php

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $defaultBranch = Branch::where('tenant_id', $tenant->id)->where('is_default', true)->first();
            if (! $defaultBranch) {
                continue;
            }

            $branchId = $defaultBranch->id;

            // Assign transactions to default branch
            if (Schema::hasTable('transactions')) {
                \Illuminate\Support\Facades\DB::table('transactions')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign purchase orders to default branch
            if (Schema::hasTable('purchase_orders')) {
                \Illuminate\Support\Facades\DB::table('purchase_orders')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign cash movements to default branch
            if (Schema::hasTable('cash_movements') && Schema::hasColumn('cash_movements', 'branch_id')) {
                \Illuminate\Support\Facades\DB::table('cash_movements')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign POS sessions to default branch
            if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'branch_id')) {
                \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign stock movements to default branch
            if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'branch_id')) {
                \Illuminate\Support\Facades\DB::table('stock_movements')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign stock audits to default branch
            if (Schema::hasTable('stock_audits') && Schema::hasColumn('stock_audits', 'branch_id')) {
                \Illuminate\Support\Facades\DB::table('stock_audits')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign customer ledger to default branch
            if (Schema::hasTable('customer_ledger') && Schema::hasColumn('customer_ledger', 'branch_id')) {
                \Illuminate\Support\Facades\DB::table('customer_ledger')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }

            // Assign users to default branch via user_branches pivot
            if (Schema::hasTable('user_branches')) {
                $users = \Illuminate\Support\Facades\DB::table('users')
                    ->where('tenant_id', $tenant->id)
                    ->pluck('id');

                foreach ($users as $userId) {
                    $alreadyAssigned = \Illuminate\Support\Facades\DB::table('user_branches')
                        ->where('user_id', $userId)
                        ->where('branch_id', $branchId)
                        ->exists();

                    if (! $alreadyAssigned) {
                        \Illuminate\Support\Facades\DB::table('user_branches')->insert([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'user_id' => $userId,
                            'branch_id' => $branchId,
                            'is_primary' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Do nothing — we never delete data in down()
    }
};
