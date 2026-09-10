<?php

namespace App\Services;

use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\DB;

class PlatformAuditService
{
    public function getLogs(array $filters = [], int $perPage = 25)
    {
        $query = PlatformAuditLog::with(['platformUser', 'tenant']);

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['platform_user_id'])) {
            $query->where('platform_user_id', $filters['platform_user_id']);
        }

        if (!empty($filters['resource_type'])) {
            $query->where('resource_type', $filters['resource_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getRecentActivity(int $limit = 20): \Illuminate\Support\Collection
    {
        return PlatformAuditLog::with(['platformUser', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTenantActivity(string $tenantId, int $limit = 50): \Illuminate\Support\Collection
    {
        return PlatformAuditLog::with(['platformUser'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getActionCounts(): array
    {
        return PlatformAuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'action')
            ->toArray();
    }
}
