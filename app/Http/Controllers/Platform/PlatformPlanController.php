<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlatformPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->orderBy('sort_order')->get();
        return view('platform.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'max_users' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'max_transactions' => 'required|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'online_store_enabled' => 'boolean',
            'custom_domain_enabled' => 'boolean',
            'advanced_reports_enabled' => 'boolean',
            'cloud_backup_enabled' => 'boolean',
            'api_access_enabled' => 'boolean',
            'multi_unit_enabled' => 'boolean',
            'efris_enabled' => 'boolean',
            'loyalty_enabled' => 'boolean',
            'wallet_enabled' => 'boolean',
            'multi_branch_enabled' => 'boolean',
        ]);

        Plan::create($data);

        return redirect()->route('platform.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $plan = Plan::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'max_users' => 'sometimes|integer|min:1',
            'max_branches' => 'sometimes|integer|min:1',
            'max_products' => 'sometimes|integer|min:1',
            'max_transactions' => 'sometimes|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'online_store_enabled' => 'boolean',
            'custom_domain_enabled' => 'boolean',
            'advanced_reports_enabled' => 'boolean',
            'cloud_backup_enabled' => 'boolean',
            'api_access_enabled' => 'boolean',
            'multi_unit_enabled' => 'boolean',
            'efris_enabled' => 'boolean',
            'loyalty_enabled' => 'boolean',
            'wallet_enabled' => 'boolean',
            'multi_branch_enabled' => 'boolean',
        ]);

        $plan->update($data);

        return redirect()->route('platform.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(string $id)
    {
        $plan = Plan::findOrFail($id);
        if ($plan->subscriptions()->exists()) {
            return redirect()->route('platform.plans.index')
                ->with('error', 'Cannot delete a plan with active subscriptions.');
        }
        $plan->delete();
        return redirect()->route('platform.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
