<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformBusinessService;
use App\Services\PlatformAuditService;
use App\Services\UsageService;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformBusinessController extends Controller
{
    public function __construct(
        private PlatformBusinessService $businessService,
        private PlatformAuditService $auditService,
        private UsageService $usageService,
    ) {}

    public function index(Request $request)
    {
        $businesses = $this->businessService->listBusinesses($request->only([
            'search', 'status', 'plan_id', 'sort_by', 'sort_dir'
        ]));

        return view('platform.businesses.index', compact('businesses'));
    }

    public function show(string $id)
    {
        $business = $this->businessService->getBusiness($id);
        $usage = $this->usageService->getTenantUsage($id);
        $activity = $this->auditService->getTenantActivity($id, 30);

        return view('platform.businesses.show', compact('business', 'usage', 'activity'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8',
            'country' => 'nullable|string|max:2',
            'currency' => 'nullable|string|max:3',
            'timezone' => 'nullable|string|max:50',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'initial_branch_name' => 'nullable|string|max:255',
        ]);

        $business = $this->businessService->createBusiness($request->all());

        return redirect()->route('platform.businesses.show', $business->id)
            ->with('success', 'Business created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,suspended,cancelled',
        ]);

        $business = $this->businessService->updateBusiness($id, $request->only(['name', 'status']));

        return redirect()->route('platform.businesses.show', $business->id)
            ->with('success', 'Business updated successfully.');
    }

    public function suspend(string $id)
    {
        $business = $this->businessService->suspendBusiness($id, request('reason'));

        return redirect()->route('platform.businesses.show', $business->id)
            ->with('success', 'Business suspended successfully.');
    }

    public function activate(string $id)
    {
        $business = $this->businessService->activateBusiness($id);

        return redirect()->route('platform.businesses.show', $business->id)
            ->with('success', 'Business activated successfully.');
    }

    public function destroy(string $id)
    {
        $business = $this->businessService->getBusiness($id);

        $tenantId = $business->id;

        $tables = [
            'transaction_payments', 'transaction_items', 'transactions',
            'purchase_items', 'purchases',
            'stock_movements', 'stock_adjustments',
            'product_variants', 'product_serials', 'products', 'categories',
            'customers', 'suppliers', 'branches',
            'loyalty_accounts', 'wallets', 'subscriptions',
            'email_accounts', 'email_logs',
            'users',
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($business, $tenantId, $tables) {
            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::table($table)->where('tenant_id', $tenantId)->delete();
                }
            }

            $business->delete();
        });

        return redirect()->route('platform.businesses.index')
            ->with('success', 'Business "' . $business->name . '" has been permanently deleted.');
    }
}
