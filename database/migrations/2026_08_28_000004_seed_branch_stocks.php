<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = '3bf275d3-285f-42be-a777-7b03e0a5a7ff';
        $mainBranchId = '21ee14d9-cd93-4fef-8480-b9006f721306';
        $downtownBranchId = '1451d6c0-c55d-4d5a-b5a7-c2d0b3179c4a';

        // Delete duplicate Main Branch
        DB::table('branches')->where('id', 'branch-main')->delete();
        DB::table('branches')->where('id', 'f0ba3d9d-5ef0-4a1f-a901-b0ea24ad05b1')->delete();

        $products = DB::table('products')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->get();

        foreach ($products as $product) {
            $totalStock = (float) $product->current_stock;
            $downtownStock = (int) ceil($totalStock * 0.3);
            $mainStock = $totalStock - $downtownStock;

            // Main branch gets ~70%
            DB::table('branch_stocks')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $mainBranchId,
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => $mainStock,
                'reserved_quantity' => 0,
                'minimum_stock' => null,
                'maximum_stock' => null,
                'average_cost' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Downtown branch gets ~30%
            DB::table('branch_stocks')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $downtownBranchId,
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => $downtownStock,
                'reserved_quantity' => 0,
                'minimum_stock' => null,
                'maximum_stock' => null,
                'average_cost' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Also create default branches for Nova tenant
        $novaTenantId = '3d98e9ae-9ef9-43fc-a980-ffbb178753e0';
        $existing = DB::table('branches')->where('tenant_id', $novaTenantId)->first();
        if ($existing) {
            $novaProducts = DB::table('products')
                ->where('tenant_id', $novaTenantId)
                ->where('is_deleted', false)
                ->get();

            foreach ($novaProducts as $product) {
                DB::table('branch_stocks')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $novaTenantId,
                    'branch_id' => $existing->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => (float) $product->current_stock,
                    'reserved_quantity' => 0,
                    'minimum_stock' => null,
                    'maximum_stock' => null,
                    'average_cost' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('branch_stocks')->delete();
    }
};
