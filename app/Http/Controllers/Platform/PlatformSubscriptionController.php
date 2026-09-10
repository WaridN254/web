<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformSubscriptionService;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformSubscriptionController extends Controller
{
    public function __construct(
        private PlatformSubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request)
    {
        $subscriptions = $this->subscriptionService->getSubscriptions($request->only([
            'status', 'plan_id'
        ]));

        return view('platform.subscriptions.index', compact('subscriptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        $this->subscriptionService->createSubscription(
            $request->tenant_id,
            $request->plan_id,
            $request->only(['billing_cycle', 'payment_method', 'notes'])
        );

        return redirect()->route('platform.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function changePlan(Request $request, string $tenantId)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $this->subscriptionService->changePlan($tenantId, $request->plan_id, $request->reason);

        return redirect()->route('platform.businesses.show', $tenantId)
            ->with('success', 'Plan changed successfully.');
    }

    public function cancel(string $tenantId)
    {
        $this->subscriptionService->cancelSubscription($tenantId, request('reason'));

        return redirect()->route('platform.businesses.show', $tenantId)
            ->with('success', 'Subscription cancelled.');
    }
}
