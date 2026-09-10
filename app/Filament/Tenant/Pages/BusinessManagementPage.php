<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Business;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Validator;

class BusinessManagementPage extends Page
{
    use HasPermission;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings');
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('navigation.business_management');
    }

    protected static ?string $slug = 'business-management';

    public string $view = 'filament.tenant.pages.business-management';

    public string $business_name = '';
    public string $business_phone = '';
    public string $business_address = '';
    public string $tin = '';
    public string $currency_code = '';
    public string $admin_email = '';
    public string $admin_phone = '';
    public string $timezone = '';
    public string $country_code = '';
    public string $locale = '';

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        $business = $tenant?->business ?? Business::first();

        if ($business) {
            $this->business_name = $business->name ?? '';
            $this->business_phone = $business->phone ?? '';
            $this->business_address = $business->address ?? '';
            $this->tin = $business->tin ?? '';
            $this->currency_code = $tenant->currency_code ?? $business->currency_code ?? 'UGX';
        }

        if ($tenant) {
            $this->admin_email = $tenant->admin_email ?? '';
            $this->admin_phone = $tenant->admin_phone ?? '';
            $this->timezone = $tenant->timezone ?? 'Africa/Kampala';
            $this->country_code = $tenant->country_code ?? 'UG';
            $this->locale = $tenant->locale ?? 'en';
        }
    }

    public function getTitle(): string
    {
        return __('navigation.business_management');
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function save(): void
    {
        $tenant = auth()->user()->tenant;
        $business = $tenant?->business ?? Business::first();

        $validator = Validator::make([
            'business_name' => $this->business_name,
            'business_phone' => $this->business_phone,
            'business_address' => $this->business_address,
            'tin' => $this->tin,
            'currency_code' => $this->currency_code,
            'admin_email' => $this->admin_email,
            'admin_phone' => $this->admin_phone,
            'timezone' => $this->timezone,
            'country_code' => $this->country_code,
            'locale' => $this->locale,
        ], [
            'business_name' => ['required', 'string', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'tin' => ['nullable', 'string', 'max:100'],
            'currency_code' => ['required', 'string', 'max:10'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Validation Error',
                'description' => collect($validator->errors()->all())->take(3)->implode("\n"),
            ]);
            return;
        }

        if ($business) {
            $business->name = $this->business_name;
            $business->phone = $this->business_phone ?: null;
            $business->address = $this->business_address ?: null;
            $business->tin = $this->tin ?: null;
            $business->currency_code = $this->currency_code;
            $business->save();
        }

        if ($tenant) {
            $tenant->currency_code = $this->currency_code;
            $tenant->admin_email = $this->admin_email ?: null;
            $tenant->admin_phone = $this->admin_phone ?: null;
            $tenant->timezone = $this->timezone ?: null;
            $tenant->country_code = $this->country_code ?: null;
            $tenant->locale = $this->locale ?: null;
            $tenant->save();
        }

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Business Updated',
            'description' => 'Your business account details have been saved.',
        ]);
    }
}