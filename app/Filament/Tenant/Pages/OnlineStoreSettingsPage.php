<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Branch;
use App\Services\OnlineStoreService;
use Filament\Pages\Page;

class OnlineStoreSettingsPage extends Page
{
    use HasPermission;

    protected static ?string $title = 'Online Store Settings';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-globe-alt';
    }

    public static function getNavigationLabel(): string
    {
        return 'Online Store';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Online Shop';
    }

    protected static ?string $slug = 'online-store-settings-page';
    protected string $view = 'filament.tenant.pages.online-store-settings';

    public bool $is_enabled = false;
    public string $store_name = '';
    public string $url_slug = 'my-store';
    public string $store_description = '';
    public string $fulfillment_strategy = 'branch_with_stock';
    public bool $allow_pickup = true;
    public bool $allow_delivery = false;
    public float $delivery_fee = 0;
    public ?float $free_delivery_threshold = null;
    public string $currency_code = 'UGX';
    public bool $tax_included = false;
    public bool $allow_guest_checkout = true;
    public bool $require_phone = true;
    public bool $show_stock_levels = false;
    public bool $allow_branch_selection = true;
    public string $contact_phone = '';
    public string $contact_email = '';
    public string $contact_address = '';

    public function mount(): void
    {
        $settings = app(OnlineStoreService::class)->getOrCreateSettings(
            auth()->user()->tenant_id ?? session('tenant_id')
        );

        $this->is_enabled = (bool) ($settings->is_enabled ?? false);
        $this->store_name = $settings->store_name ?? '';
        $this->url_slug = $settings->url_slug ?? \Illuminate\Support\Str::slug($settings->store_name ?? 'my-store');
        $this->store_description = $settings->store_description ?? '';
        $this->fulfillment_strategy = $settings->fulfillment_strategy ?? 'branch_with_stock';
        $this->allow_pickup = (bool) ($settings->allow_pickup ?? true);
        $this->allow_delivery = (bool) ($settings->allow_delivery ?? false);
        $this->delivery_fee = (float) ($settings->delivery_fee ?? 0);
        $this->free_delivery_threshold = $settings->free_delivery_threshold ? (float) $settings->free_delivery_threshold : null;
        $this->currency_code = $settings->currency_code ?? 'UGX';
        $this->tax_included = (bool) ($settings->tax_included ?? false);
        $this->allow_guest_checkout = (bool) ($settings->allow_guest_checkout ?? true);
        $this->require_phone = (bool) ($settings->require_phone ?? true);
        $this->show_stock_levels = (bool) ($settings->show_stock_levels ?? false);
        $this->allow_branch_selection = (bool) ($settings->allow_branch_selection ?? true);

        $contact = $settings->contact_info ?? [];
        $this->contact_phone = $contact['phone'] ?? '';
        $this->contact_email = $contact['email'] ?? '';
        $this->contact_address = $contact['address'] ?? '';
    }

    public function save(): void
    {
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $settings = app(OnlineStoreService::class)->getOrCreateSettings($tenantId);

        $settings->update([
            'is_enabled' => $this->is_enabled,
            'store_name' => $this->store_name,
            'url_slug' => $this->url_slug,
            'store_description' => $this->store_description,
            'fulfillment_strategy' => $this->fulfillment_strategy,
            'allow_pickup' => $this->allow_pickup,
            'allow_delivery' => $this->allow_delivery,
            'delivery_fee' => $this->delivery_fee,
            'free_delivery_threshold' => $this->free_delivery_threshold,
            'currency_code' => $this->currency_code,
            'tax_included' => $this->tax_included,
            'allow_guest_checkout' => $this->allow_guest_checkout,
            'require_phone' => $this->require_phone,
            'show_stock_levels' => $this->show_stock_levels,
            'allow_branch_selection' => $this->allow_branch_selection,
            'contact_info' => [
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
                'address' => $this->contact_address,
            ],
        ]);

        $this$this->dispatch('gooey-toast', [
            'type' => 'success',
            'title' => 'Online Store Settings',
            'description' => 'Settings saved successfully.',
        ]);
    }
}
