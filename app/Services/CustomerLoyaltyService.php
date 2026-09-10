<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLoyaltyService
{
    public function getOrCreateAccount(string $tenantId, string $customerId): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            ['tenant_id' => $tenantId, 'customer_id' => $customerId],
            ['id' => (string) Str::uuid(), 'balance' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
        );
    }

    public function getBalance(string $tenantId, string $customerId): int
    {
        $account = $this->getOrCreateAccount($tenantId, $customerId);
        return $account->balance;
    }

    public function earnPoints(
        string $tenantId,
        string $customerId,
        int $points,
        ?string $branchId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): LoyaltyAccount {
        return DB::transaction(function () use ($tenantId, $customerId, $points, $branchId, $referenceType, $referenceId, $description) {
            $account = $this->getOrCreateAccount($tenantId, $customerId);

            $account->increment('balance', $points);
            $account->increment('total_earned', $points);

            LoyaltyTransaction::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'loyalty_account_id' => $account->id,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'type' => 'earn',
                'points' => $points,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Earned {$points} points",
            ]);

            return $account->fresh();
        });
    }

    public function redeemPoints(
        string $tenantId,
        string $customerId,
        int $points,
        ?string $branchId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): LoyaltyAccount {
        return DB::transaction(function () use ($tenantId, $customerId, $points, $branchId, $referenceType, $referenceId, $description) {
            $account = $this->getOrCreateAccount($tenantId, $customerId);

            if ($account->balance < $points) {
                throw new \RuntimeException('Insufficient loyalty points. Available: ' . $account->balance);
            }

            $account->decrement('balance', $points);
            $account->increment('total_redeemed', $points);

            LoyaltyTransaction::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'loyalty_account_id' => $account->id,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'type' => 'redeem',
                'points' => -$points,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Redeemed {$points} points",
            ]);

            return $account->fresh();
        });
    }

    public function getTransactions(string $tenantId, string $customerId, ?string $branchId = null)
    {
        $query = LoyaltyTransaction::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
