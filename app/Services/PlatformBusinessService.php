<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlatformBusinessService
{
    public function listBusinesses(array $filters = [], int $perPage = 25)
    {
        $query = Tenant::with(['business', 'subscription.plan']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['plan_id'])) {
            $query->whereHas('subscription', fn($q) => $q->where('plan_id', $filters['plan_id']));
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function getBusiness(string $id): Tenant
    {
        return Tenant::with(['business', 'subscription.plan', 'branches', 'users.role'])->findOrFail($id);
    }

    public function createBusiness(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['business_name'],
                'slug' => Str::slug($data['business_name']),
                'status' => 'active',
                'trial_ends_at' => now()->addDays($data['trial_days'] ?? 14),
            ]);

            $ownerRole = \App\Models\Role::where('name', 'owner')->first();
            $owner = User::create([
                'full_name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => $data['owner_phone'] ?? null,
                'password_hash' => bcrypt($data['password'] ?? Str::random(12)),
                'role_id' => $ownerRole?->id,
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'language' => $data['language'] ?? 'en',
                'locale' => $data['locale'] ?? 'en',
                'timezone' => $data['timezone'] ?? 'Africa/Kampala',
            ]);

            $branch = Branch::create([
                'name' => $data['initial_branch_name'] ?? 'Main Branch',
                'tenant_id' => $tenant->id,
                'business_id' => $tenant->business_id,
                'is_default' => true,
                'is_active' => true,
            ]);

            $owner->update(['default_branch_id' => $branch->id]);

            PlatformAuditLog::log('business_created', null, $tenant->id, 'Tenant', $tenant->id, "Business '{$tenant->name}' created");

            return $tenant->fresh(['business', 'subscription.plan', 'branches']);
        });
    }

    public function updateBusiness(string $id, array $data): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($data);

        PlatformAuditLog::log('business_updated', null, $tenant->id, 'Tenant', $tenant->id, "Business '{$tenant->name}' updated");

        return $tenant->fresh();
    }

    public function suspendBusiness(string $id, ?string $reason = null): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);

        $subscription = $tenant->subscription;
        if ($subscription && $subscription->isActive()) {
            $subscription->update(['status' => 'suspended']);
        }

        PlatformAuditLog::log('business_suspended', null, $tenant->id, 'Tenant', $tenant->id, "Business '{$tenant->name}' suspended", ['reason' => $reason]);

        return $tenant->fresh();
    }

    public function activateBusiness(string $id): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);

        $subscription = $tenant->subscription;
        if ($subscription && $subscription->status === 'suspended') {
            $subscription->update(['status' => 'active']);
        }

        PlatformAuditLog::log('business_activated', null, $tenant->id, 'Tenant', $tenant->id, "Business '{$tenant->name}' activated");

        return $tenant->fresh();
    }

    public function getBusinessStats(): array
    {
        return [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'trial' => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'cancelled' => Tenant::where('status', 'cancelled')->count(),
            'new_this_month' => Tenant::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    public function getUsageStats(string $tenantId): array
    {
        $tenant = Tenant::findOrFail($tenantId);
        return [
            'users' => $tenant->users()->count(),
            'branches' => $tenant->branches()->count(),
            'products' => DB::table('products')->where('tenant_id', $tenantId)->count(),
            'customers' => DB::table('customers')->where('tenant_id', $tenantId)->count(),
            'transactions' => DB::table('transactions')->where('tenant_id', $tenantId)->count(),
        ];
    }
}
