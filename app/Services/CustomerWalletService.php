<?php

namespace App\Services;

use App\Models\CustomerWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerWalletService
{
    public function getOrCreateWallet(string $tenantId, string $customerId): CustomerWallet
    {
        return CustomerWallet::firstOrCreate(
            ['tenant_id' => $tenantId, 'customer_id' => $customerId],
            ['id' => (string) Str::uuid(), 'balance' => 0, 'total_deposited' => 0, 'total_used' => 0]
        );
    }

    public function getBalance(string $tenantId, string $customerId): float
    {
        $wallet = $this->getOrCreateWallet($tenantId, $customerId);
        return (float) $wallet->balance;
    }

    public function deposit(
        string $tenantId,
        string $customerId,
        float $amount,
        ?string $branchId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): CustomerWallet {
        return DB::transaction(function () use ($tenantId, $customerId, $amount, $branchId, $referenceType, $referenceId, $description) {
            $wallet = $this->getOrCreateWallet($tenantId, $customerId);

            $wallet->increment('balance', $amount);
            $wallet->increment('total_deposited', $amount);

            $newBalance = (float) $wallet->fresh()->balance;

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Deposited " . number_format($amount, 2),
            ]);

            return $wallet->fresh();
        });
    }

    public function useFunds(
        string $tenantId,
        string $customerId,
        float $amount,
        ?string $branchId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): CustomerWallet {
        return DB::transaction(function () use ($tenantId, $customerId, $amount, $branchId, $referenceType, $referenceId, $description) {
            $wallet = $this->getOrCreateWallet($tenantId, $customerId);

            if ((float) $wallet->balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance. Available: ' . number_format($wallet->balance, 2));
            }

            $wallet->decrement('balance', $amount);
            $wallet->increment('total_used', $amount);

            $newBalance = (float) $wallet->fresh()->balance;

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'type' => 'usage',
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Used " . number_format($amount, 2) . " from wallet",
            ]);

            return $wallet->fresh();
        });
    }

    public function refund(
        string $tenantId,
        string $customerId,
        float $amount,
        ?string $branchId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): CustomerWallet {
        return DB::transaction(function () use ($tenantId, $customerId, $amount, $branchId, $referenceType, $referenceId, $description) {
            $wallet = $this->getOrCreateWallet($tenantId, $customerId);

            $wallet->increment('balance', $amount);

            $newBalance = (float) $wallet->fresh()->balance;

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'type' => 'refund',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Refunded " . number_format($amount, 2) . " to wallet",
            ]);

            return $wallet->fresh();
        });
    }

    public function getTransactions(string $tenantId, string $customerId, ?string $branchId = null)
    {
        $query = WalletTransaction::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
