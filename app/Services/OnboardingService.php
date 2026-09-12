<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\OnboardingStep;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    public const STEPS = [
        'business' => ['label' => 'Business Details', 'required' => true],
        'branch' => ['label' => 'First Branch', 'required' => true],
        'pos' => ['label' => 'POS Basics', 'required' => true],
        'tax' => ['label' => 'Tax Setup', 'required' => false],
        'receipt' => ['label' => 'Receipt Setup', 'required' => false],
        'payment_methods' => ['label' => 'Payment Methods', 'required' => false],
        'products' => ['label' => 'Add Products', 'required' => false],
        'opening_stock' => ['label' => 'Opening Stock', 'required' => false],
        'team' => ['label' => 'Invite Team', 'required' => false],
        'hardware' => ['label' => 'Hardware', 'required' => false],
    ];

    public function getOrCreateForTenant(string $tenantId): OnboardingStep
    {
        return OnboardingStep::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['current_step' => 'business']
        );
    }

    public function getProgress(string $tenantId): array
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        $total = count(self::STEPS);
        $completed = 0;
        $steps = [];

        foreach (self::STEPS as $key => $config) {
            $field = $key . '_completed';
            $isComplete = (bool) $onboarding->$field;
            if ($isComplete) $completed++;
            $steps[$key] = [
                'label' => $config['label'],
                'required' => $config['required'],
                'completed' => $isComplete,
            ];
        }

        return [
            'current_step' => $onboarding->current_step,
            'completed_count' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'is_complete' => $onboarding->completed_at !== null,
            'required_remaining' => collect($steps)->where('required', true)->where('completed', false)->count(),
            'steps' => $steps,
        ];
    }

    public function completeStep(string $tenantId, string $step): void
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        $field = $step . '_completed';
        if (in_array($field, $this->getStepFields())) {
            $onboarding->update([$field => true]);
        }
        $this->advanceCurrentStep($tenantId);
    }

    public function setCurrentStep(string $tenantId, string $step): void
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        if (array_key_exists($step, self::STEPS)) {
            $onboarding->update(['current_step' => $step]);
        }
    }

    public function getNextStep(string $tenantId): ?string
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        $stepKeys = array_keys(self::STEPS);
        $currentIndex = array_search($onboarding->current_step, $stepKeys);
        if ($currentIndex === false || $currentIndex >= count($stepKeys) - 1) {
            return null;
        }
        return $stepKeys[$currentIndex + 1];
    }

    public function getPrevStep(string $tenantId): ?string
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        $stepKeys = array_keys(self::STEPS);
        $currentIndex = array_search($onboarding->current_step, $stepKeys);
        if ($currentIndex <= 0) {
            return null;
        }
        return $stepKeys[$currentIndex - 1];
    }

    public function canComplete(string $tenantId): bool
    {
        $progress = $this->getProgress($tenantId);
        return $progress['required_remaining'] === 0;
    }

    public function markComplete(string $tenantId): void
    {
        $onboarding = $this->getOrCreateForTenant($tenantId);
        $onboarding->update(['completed_at' => now()]);
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenant->update(['status' => 'active']);
        }
    }

    public function skipStep(string $tenantId, string $step): void
    {
        $this->advanceCurrentStep($tenantId);
    }

    private function advanceCurrentStep(string $tenantId): void
    {
        $next = $this->getNextStep($tenantId);
        if ($next) {
            $this->setCurrentStep($tenantId, $next);
        }
    }

    private function getStepFields(): array
    {
        return array_map(fn($s) => $s . '_completed', array_keys(self::STEPS));
    }

    public function createFreeSubscription(string $tenantId): Subscription
    {
        $plan = Plan::where('slug', 'starter')->first();
        if (!$plan) {
            $plan = Plan::where('is_default', true)->first();
        }
        if (!$plan) {
            $plan = Plan::create([
                'name' => 'Free',
                'slug' => 'free',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'is_active' => true,
                'is_default' => true,
                'max_users' => 3,
                'max_branches' => 1,
                'max_products' => 100,
                'max_transactions' => 1000,
                'trial_days' => 0,
            ]);
        }

        return Subscription::create([
            'tenant_id' => $tenantId,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 0,
            'currency' => $plan->currency ?? 'USD',
            'starts_at' => now(),
            'ends_at' => null,
        ]);
    }

    public function assignOwnerRole(User $user): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $user->update(['role_id' => $ownerRole->id]);
        }
    }
}
