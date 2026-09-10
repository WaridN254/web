<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = '3bf275d3-285f-42be-a777-7b03e0a5a7ff';

        // Remove duplicate "Main Branch" (keep only the UUID-based one)
        DB::table('branches')
            ->where('id', 'branch-main')
            ->delete();

        // Add Warehouse branch
        $warehouseId = Str::uuid()->toString();
        DB::table('branches')->insert([
            'id' => $warehouseId,
            'tenant_id' => $tenantId,
            'name' => 'Warehouse',
            'code' => 'WH',
            'address' => 'Industrial Area, Kampala',
            'phone' => '+256700020002',
            'email' => 'warehouse@newbusiness.com',
            'city' => 'Kampala',
            'country' => 'Uganda',
            'timezone' => 'Africa/Kampala',
            'is_active' => true,
            'is_default' => false,
            'currency_code' => 'UGX',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create branch stocks for the Warehouse using existing products
        $products = DB::table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->get();

        foreach ($products as $product) {
            $qty = (float) $product->current_stock;
            $halfQty = round($qty / 2);

            // Move half stock to Warehouse
            if ($halfQty > 0) {
                DB::table('branch_stocks')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $warehouseId,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => $halfQty,
                    'reserved_quantity' => 0,
                    'average_cost' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update main branch stock to the other half
                DB::table('branch_stocks')
                    ->where('branch_id', '21ee14d9-cd93-4fef-8480-b9006f721306')
                    ->where('product_id', $product->id)
                    ->update(['quantity' => $qty - $halfQty]);
            }
        }

        // Assign Manager user to Warehouse too
        $managerId = '2d347ee1-3eb3-47d2-9c5d-c0cca3abcb0f';
        if (!DB::table('user_branches')->where('user_id', $managerId)->where('branch_id', $warehouseId)->exists()) {
            DB::table('user_branches')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $managerId,
                'branch_id' => $warehouseId,
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $tenantId = '3bf275d3-285f-42be-a777-7b03e0a5a7ff';

        DB::table('user_branches')->where('branch_id', DB::table('branches')->where('tenant_id', $tenantId)->where('code', 'WH')->value('id'))->delete();
        DB::table('branch_stocks')->where('branch_id', DB::table('branches')->where('tenant_id', $tenantId)->where('code', 'WH')->value('id'))->delete();
        DB::table('branches')->where('tenant_id', $tenantId)->where('code', 'WH')->delete();
    }
};
