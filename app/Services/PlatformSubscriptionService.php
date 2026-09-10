<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\DB;

class PlatformSubscriptionService
{
    public function getSubscriptions(array $filters = [], int $perPage = 25)
    {
        $query = Subscription::with(['tenant', 'plan']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['plan_id'])) {
            $query->where('plan_id', $filters['plan_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createSubscription(string $tenantId, string $planId, array $data = []): Subscription
    {
        $plan = Plan::findOrFail($planId);
        $tenant = Tenant::findOrFail($tenantId);

        return DB::transaction(function () use ($tenant, $plan, $data) {
            $oldSubscription = $tenant->subscription;
            if ($oldSubscription && $oldSubscription->isActive()) {
                $oldSubscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            }

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
                'price' => $data['billing_cycle'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly,
                'currency' => $plan->currency,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'payment_method' => $data['payment_method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => Invoice::generateNumber(),
                'status' => 'open',
                'subtotal' => $subscription->price,
                'total' => $subscription->price,
                'amount_due' => $subscription->price,
                'currency' => $subscription->currency,
                'billing_reason' => 'subscription_create',
                'due_date' => now()->addDays(7),
            ]);

            PlatformAuditLog::log('subscription_created', null, $tenant->id, 'Subscription', $subscription->id, "Subscription to '{$plan->name}' created for '{$tenant->name}'");

            return $subscription->fresh(['tenant', 'plan']);
        });
    }

    public function changePlan(string $tenantId, string $newPlanId, ?string $reason = null): Subscription
    {
        $tenant = Tenant::findOrFail($tenantId);
        $newPlan = Plan::findOrFail($newPlanId);
        $oldSubscription = $tenant->subscription;

        return DB::transaction(function () use ($tenant, $newPlan, $oldSubscription, $reason) {
            $oldPlanName = $oldSubscription?->plan?->name ?? 'None';

            if ($oldSubscription) {
                $oldSubscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            }

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $newPlan->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'price' => $newPlan->price_monthly,
                'currency' => $newPlan->currency,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            PlatformAuditLog::log('plan_changed', null, $tenant->id, 'Subscription', $subscription->id, "Plan changed from '{$oldPlanName}' to '{$newPlan->name}'", ['reason' => $reason]);

            return $subscription->fresh(['tenant', 'plan']);
        });
    }

    public function cancelSubscription(string $tenantId, ?string $reason = null): ?Subscription
    {
        $tenant = Tenant::findOrFail($tenantId);
        $subscription = $tenant->subscription;

        if (!$subscription) return null;

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        PlatformAuditLog::log('subscription_cancelled', null, $tenant->id, 'Subscription', $subscription->id, "Subscription cancelled for '{$tenant->name}'", ['reason' => $reason]);

        return $subscription->fresh(['tenant', 'plan']);
    }

    public function getSubscriptionStats(): array
    {
        return [
            'active' => Subscription::where('status', 'active')->count(),
            'trialing' => Subscription::where('status', 'trialing')->count(),
            'expiring_soon' => Subscription::where('status', 'active')
                ->where('ends_at', '<=', now()->addDays(7))
                ->where('ends_at', '>', now())
                ->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
        ];
    }

    public function getRevenueStats(): array
    {
        $now = now();
        return [
            'mrr' => Subscription::where('status', 'active')->sum('price'),
            'revenue_this_month' => Payment::where('status', 'completed')
                ->where('paid_at', '>=', $now->startOfMonth())
                ->sum('amount'),
            'revenue_this_year' => Payment::where('status', 'completed')
                ->where('paid_at', '>=', $now->startOfYear())
                ->sum('amount'),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];
    }
}
