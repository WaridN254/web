<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\FeatureFlag;
use App\Models\TenantFeatureFlag;

class FeatureEntitlementService
{
    public function canUseFeature(string $tenantId, string $feature): bool
    {
        $globalFlag = FeatureFlag::where('slug', $feature)->first();
        if ($globalFlag && !$globalFlag->is_enabled) return false;

        $tenantFlag = TenantFeatureFlag::where('tenant_id', $tenantId)
            ->whereHas('featureFlag', fn($q) => $q->where('slug', $feature))
            ->first();
        if ($tenantFlag) return $tenantFlag->is_enabled;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return false;

        $subscription = $tenant->subscription;
        if (!$subscription || !$subscription->isActive()) return false;

        $plan = $subscription->plan;
        if (!$plan) return false;

        return match ($feature) {
            'online_store' => $plan->online_store_enabled,
            'custom_domain' => $plan->custom_domain_enabled,
            'advanced_reports' => $plan->advanced_reports_enabled,
            'cloud_backup' => $plan->cloud_backup_enabled,
            'api_access' => $plan->api_access_enabled,
            'multi_unit' => $plan->multi_unit_enabled,
            'efris' => $plan->efris_enabled,
            'loyalty' => $plan->loyalty_enabled,
            'wallet' => $plan->wallet_enabled,
            'multi_branch' => $plan->multi_branch_enabled,
            default => false,
        };
    }

    public function withinLimit(string $tenantId, string $resource): bool
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return false;

        $subscription = $tenant->subscription;
        if (!$subscription || !$subscription->isActive()) return false;

        $plan = $subscription->plan;
        if (!$plan) return false;

        $current = match ($resource) {
            'users' => $tenant->users()->count(),
            'branches' => $tenant->branches()->count(),
            'products' => \DB::table('products')->where('tenant_id', $tenantId)->count(),
            default => 0,
        };

        $limit = match ($resource) {
            'users' => $plan->max_users,
            'branches' => $plan->max_branches,
            'products' => $plan->max_products,
            default => PHP_INT_MAX,
        };

        return $current < $limit;
    }

    public function getUsage(string $tenantId, string $resource): array
    {
        $tenant = Tenant::find($tenantId);
        $subscription = $tenant?->subscription;
        $plan = $subscription?->plan;

        $current = match ($resource) {
            'users' => $tenant?->users()->count() ?? 0,
            'branches' => $tenant?->branches()->count() ?? 0,
            'products' => \DB::table('products')->where('tenant_id', $tenantId)->count(),
            default => 0,
        };

        $limit = match ($resource) {
            'users' => $plan?->max_users ?? 0,
            'branches' => $plan?->max_branches ?? 0,
            'products' => $plan?->max_products ?? 0,
            default => 0,
        ];

        return ['current' => $current, 'limit' => $limit, 'percentage' => $limit > 0 ? round(($current / $limit) * 100) : 0];
    }

    public function setTenantFeature(string $tenantId, string $featureSlug, bool $enabled): TenantFeatureFlag
    {
        $flag = FeatureFlag::where('slug', $featureSlug)->firstOrFail();
        return TenantFeatureFlag::updateOrCreate(
            ['tenant_id' => $tenantId, 'feature_flag_id' => $flag->id],
            ['is_enabled' => $enabled]
        );
    }
}
