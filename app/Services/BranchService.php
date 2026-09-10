<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchStock;
use Illuminate\Support\Facades\Session;

class BranchService
{
    /** @var array<string, Branch|null> Static cache keyed by user ID (survives within process) */
    private static array $resolvedBranches = [];

    /**
     * Get the current active branch for the authenticated user.
     * Results are cached per-request and per-process to avoid repeated DB lookups.
     */
    public function getActiveBranch(): ?Branch
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $userId = (string) $user->id;
        $cacheKey = 'resolved_branch_' . $userId;

        // 1) Request-level cache (fastest — survives all calls within one HTTP request)
        if (request()->attributes->has($cacheKey)) {
            return request()->attributes->get($cacheKey);
        }

        // 2) Static property cache (survives within the same PHP process, e.g. queued jobs)
        if (array_key_exists($userId, self::$resolvedBranches)) {
            $branch = self::$resolvedBranches[$userId];
            request()->attributes->set($cacheKey, $branch);
            return $branch;
        }

        // 3) Resolve from DB
        $branch = $this->resolveBranchFromDb($user);

        // 4) Store in both caches
        self::$resolvedBranches[$userId] = $branch;
        request()->attributes->set($cacheKey, $branch);

        return $branch;
    }

    /**
     * Resolve the active branch from DB (session, default, assigned, tenant default).
     */
    private function resolveBranchFromDb($user): ?Branch
    {
        $branchId = Session::get('active_branch_id');

        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('is_active', true)
                ->first();

            if ($branch && $this->userCanAccessBranch($branch)) {
                return $branch;
            }
        }

        // Fall back to user's default branch
        if ($user->default_branch_id) {
            $branch = Branch::where('id', $user->default_branch_id)
                ->where('is_active', true)
                ->first();

            if ($branch) {
                Session::put('active_branch_id', $branch->id);
                return $branch;
            }
        }

        // Fall back to user's first explicitly assigned branch
        $branch = $user->branches()->where('is_active', true)->first();
        if ($branch) {
            Session::put('active_branch_id', $branch->id);
            return $branch;
        }

        // Fall back to tenant's default branch (only for admins/owners)
        if ($user->tenant_id
            && ($user->can_view_all_branches || ($user->role && in_array(strtolower($user->role->name), ['owner', 'admin', 'super admin'])))
        ) {
            $branch = Branch::where('tenant_id', $user->tenant_id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();

            if ($branch) {
                Session::put('active_branch_id', $branch->id);
                return $branch;
            }
        }

        return null;
    }

    /**
     * Clear the cached branch for the given user (or current user).
     * Call this when the user switches branches.
     */
    public static function clearCache(?string $userId = null): void
    {
        $userId = $userId ?? (string) auth()->id();

        unset(self::$resolvedBranches[$userId]);

        if (request()->attributes->has('resolved_branch_' . $userId)) {
            request()->attributes->remove('resolved_branch_' . $userId);
        }
    }

    /**
     * Set the active branch for the current session.
     */
    public function setActiveBranch(string $branchId): bool
    {
        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if (! $branch) {
            return false;
        }

        if (! $this->userCanAccessBranch($branch)) {
            return false;
        }

        Session::put('active_branch_id', $branch->id);
        self::clearCache();
        return true;
    }

    /**
     * Get all branches the current user can access.
     */
    public function getUserBranches()
    {
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        // Owners, admins, and users with can_view_all_branches see everything
        if ($user->can_view_all_branches
            || ($user->role && in_array(strtolower($user->role->name), ['owner', 'admin', 'super admin']))
        ) {
            return Branch::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->get();
        }

        // Users without can_view_all_branches only see their explicitly assigned branches
        return Branch::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('user_branches.user_id', $user->id);
            })
            ->get();
    }

    /**
     * Check if the current user can access a specific branch.
     */
    public function userCanAccessBranch(Branch $branch): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Owner/admin can access all branches
        if ($user->can_view_all_branches
            || ($user->role && in_array(strtolower($user->role->name), ['owner', 'admin', 'super admin']))
        ) {
            return true;
        }

        // Check if user is explicitly assigned to this branch
        return $user->branches()->where('branch_id', $branch->id)->exists();
    }

    /**
     * Get the branch stock for a specific product.
     */
    public function getBranchStock(string $branchId, string $productId, ?string $variantId = null): float
    {
        $stock = BranchStock::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        return $stock ? (float) $stock->quantity : 0;
    }

    /**
     * Check if a branch has enough stock for a sale.
     */
    public function hasEnoughStock(string $branchId, string $productId, float $quantity, ?string $variantId = null): bool
    {
        $available = $this->getBranchStock($branchId, $productId, $variantId);
        return $available >= $quantity;
    }

    /**
     * Get the active branch ID (convenience method).
     */
    public function getActiveBranchId(): ?string
    {
        $branch = $this->getActiveBranch();
        return $branch?->id;
    }
}
