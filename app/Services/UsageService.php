<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class UsageService
{
    public function getTenantUsage(string $tenantId): array
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return [];

        $subscription = $tenant->subscription;
        $plan = $subscription?->plan;

        return [
            'users' => $this->getResourceUsage($tenantId, 'users', $plan?->max_users),
            'branches' => $this->getResourceUsage($tenantId, 'branches', $plan?->max_branches),
            'products' => $this->getResourceUsage($tenantId, 'products', $plan?->max_products),
            'customers' => $this->getResourceUsage($tenantId, 'customers'),
            'transactions' => $this->getResourceUsage($tenantId, 'transactions'),
        ];
    }

    public function getGlobalUsage(): array
    {
        return [
            'total_branches' => DB::table('branches')->where('is_active', true)->count(),
            'total_users' => DB::table('users')->where('is_active', true)->count(),
            'total_products' => DB::table('products')->count(),
            'total_customers' => DB::table('customers')->count(),
            'total_transactions' => DB::table('transactions')->count(),
        ];
    }

    private function getResourceUsage(string $tenantId, string $resource, ?int $limit = null): array
    {
        $current = match ($resource) {
            'users' => DB::table('users')->where('tenant_id', $tenantId)->count(),
            'branches' => DB::table('branches')->where('tenant_id', $tenantId)->count(),
            'products' => DB::table('products')->where('tenant_id', $tenantId)->count(),
            'customers' => DB::table('customers')->where('tenant_id', $tenantId)->count(),
            'transactions' => DB::table('transactions')->where('tenant_id', $tenantId)->count(),
            default => 0,
        };

        return [
            'current' => $current,
            'limit' => $limit,
            'percentage' => $limit ? round(($current / $limit) * 100) : null,
        ];
    }
}
