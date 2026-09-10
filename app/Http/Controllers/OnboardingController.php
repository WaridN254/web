<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService,
    ) {}

    public function index()
    {
        $tenant = auth()->user()->tenant;
        $progress = $this->onboardingService->getProgress($tenant->id);

        if ($progress['is_complete']) {
            return redirect('/tenant');
        }

        return view('onboarding.index', compact('tenant', 'progress'));
    }

    public function step(string $step)
    {
        $tenant = auth()->user()->tenant;
        $progress = $this->onboardingService->getProgress($tenant->id);

        if ($progress['is_complete']) {
            return redirect('/tenant');
        }

        $this->onboardingService->setCurrentStep($tenant->id, $step);

        return view('onboarding.steps.' . $step, compact('tenant', 'progress'));
    }

    public function saveBusiness(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'business_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'currency' => 'required|string|max:3',
            'timezone' => 'required|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
        ]);

        $settings = array_merge($tenant->settings ?? [], [
            'address' => $request->address,
            'city' => $request->city,
            'tin_number' => $request->tin_number,
            'registration_number' => $request->registration_number,
        ]);

        $tenant->update([
            'name' => $request->business_name,
            'default_currency' => $request->currency,
            'timezone' => $request->timezone,
            'settings' => $settings,
        ]);

        $this->onboardingService->completeStep($tenant->id, 'business');

        return redirect()->route('onboarding.step', 'branch');
    }

    public function saveBranch(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'branch_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $branch = Branch::create([
            'name' => $request->branch_name,
            'tenant_id' => $tenant->id,
            'business_id' => $tenant->business_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_default' => true,
            'is_active' => true,
        ]);

        auth()->user()->update(['default_branch_id' => $branch->id]);
        $this->onboardingService->completeStep($tenant->id, 'branch');

        return redirect()->route('onboarding.step', 'pos');
    }

    public function savePos(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'currency' => 'required|string|max:3',
            'price_includes_tax' => 'boolean',
            'allow_negative_stock' => 'boolean',
            'allow_decimal_quantities' => 'boolean',
        ]);

        $settings = array_merge($tenant->settings ?? [], [
            'pos_currency' => $request->currency,
            'price_includes_tax' => $request->boolean('price_includes_tax'),
            'allow_negative_stock' => $request->boolean('allow_negative_stock'),
            'allow_decimal_quantities' => $request->boolean('allow_decimal_quantities'),
        ]);

        $tenant->update(['settings' => $settings]);
        $this->onboardingService->completeStep($tenant->id, 'pos');

        return redirect()->route('onboarding.step', 'tax');
    }

    public function saveTax(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'charges_tax' => 'boolean',
            'tax_name' => 'nullable|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
        ]);

        $settings = array_merge($tenant->settings ?? [], [
            'charges_tax' => $request->boolean('charges_tax'),
            'tax_name' => $request->tax_name,
            'tax_rate' => $request->tax_rate,
            'tax_inclusive' => $request->boolean('tax_inclusive'),
        ]);

        $tenant->update(['settings' => $settings]);
        $this->onboardingService->completeStep($tenant->id, 'tax');

        return redirect()->route('onboarding.step', 'receipt');
    }

    public function saveReceipt(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'receipt_footer' => 'nullable|string|max:500',
            'show_tax_number' => 'boolean',
            'show_cashier_name' => 'boolean',
            'show_customer_name' => 'boolean',
        ]);

        $settings = array_merge($tenant->settings ?? [], [
            'receipt_footer' => $request->receipt_footer,
            'show_tax_number' => $request->boolean('show_tax_number'),
            'show_cashier_name' => $request->boolean('show_cashier_name'),
            'show_customer_name' => $request->boolean('show_customer_name'),
        ]);

        $tenant->update(['settings' => $settings]);
        $this->onboardingService->completeStep($tenant->id, 'receipt');

        return redirect()->route('onboarding.step', 'payment_methods');
    }

    public function savePaymentMethods(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'methods' => 'required|array',
            'methods.*' => 'string',
        ]);

        $settings = array_merge($tenant->settings ?? [], [
            'payment_methods' => $request->methods,
        ]);

        $tenant->update(['settings' => $settings]);
        $this->onboardingService->completeStep($tenant->id, 'payment_methods');

        return redirect()->route('onboarding.step', 'products');
    }

    public function saveProducts(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $this->onboardingService->completeStep($tenant->id, 'products');
        return redirect()->route('onboarding.step', 'opening_stock');
    }

    public function saveOpeningStock(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'has_stock' => 'boolean',
        ]);

        if (!$request->boolean('has_stock')) {
            $this->onboardingService->completeStep($tenant->id, 'opening_stock');
            return redirect()->route('onboarding.step', 'team');
        }

        return redirect()->route('onboarding.step', 'opening_stock');
    }

    public function saveTeam(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $this->onboardingService->completeStep($tenant->id, 'team');
        return redirect()->route('onboarding.step', 'hardware');
    }

    public function saveHardware(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $this->onboardingService->completeStep($tenant->id, 'hardware');

        if ($this->onboardingService->canComplete($tenant->id)) {
            $this->onboardingService->markComplete($tenant->id);
            return redirect()->route('onboarding.complete');
        }

        return redirect()->route('onboarding.index');
    }

    public function skip(string $step)
    {
        $tenant = auth()->user()->tenant;
        $this->onboardingService->skipStep($tenant->id, $step);

        $next = $this->onboardingService->getNextStep($tenant->id);
        if ($next) {
            return redirect()->route('onboarding.step', $next);
        }

        if ($this->onboardingService->canComplete($tenant->id)) {
            $this->onboardingService->markComplete($tenant->id);
            return redirect()->route('onboarding.complete');
        }

        return redirect()->route('onboarding.index');
    }

    public function complete()
    {
        $tenant = auth()->user()->tenant;
        $progress = $this->onboardingService->getProgress($tenant->id);

        return view('onboarding.complete', compact('tenant', 'progress'));
    }
}
