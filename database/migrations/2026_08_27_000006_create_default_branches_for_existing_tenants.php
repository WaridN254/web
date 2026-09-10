<?php

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $existingDefault = Branch::where('tenant_id', $tenant->id)->where('is_default', true)->first();
            if ($existingDefault) {
                continue;
            }

            $businessName = $tenant->business?->name ?? $tenant->name ?? 'Business';
            $businessCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $businessName), 0, 3));

            Branch::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'business_id' => $tenant->business_id,
                'name' => $businessName . ' - Main Branch',
                'code' => $businessCode . '-MAIN',
                'address' => $tenant->business?->address ?? null,
                'phone' => $tenant->business?->phone ?? null,
                'is_active' => true,
                'is_default' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Do nothing — we never delete data in down()
    }
};
