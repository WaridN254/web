<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\CurrencyService;
use Filament\Pages\Page;

class ExchangeRatesPage extends Page
{
    use HasPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings');
    }

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('navigation.exchange_rates');
    }

    protected static ?string $slug = 'exchange-rates';

    public string $view = 'filament.tenant.pages.exchange-rates';

    public array $rates = [];
    public array $currencies = [];
    public string $base_currency = 'USD';
    public string $target_currency = 'UGX';
    public string $rate_value = '';
    public string $provider = 'manual';
    public ?string $editingId = null;

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        $this->base_currency = $tenant?->default_currency ?? 'USD';
        $this->loadRates();
        $this->currencies = Currency::where('is_active', true)->orderBy('name')->get()->toArray();
    }

    public function loadRates(): void
    {
        $tenantId = auth()->user()->tenant_id;
        $this->rates = ExchangeRate::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->orderByDesc('effective_at')->get()->toArray();
    }

    public function saveRate(): void
    {
        if (!$this->base_currency || !$this->target_currency || !$this->rate_value) {
            $this$this->dispatch('gooey-toast', ['type' => 'warning', 'title' => 'Please fill all fields']);
            return;
        }

        $rate = (float) $this->rate_value;
        if ($rate <= 0) {
            $this$this->dispatch('gooey-toast', ['type' => 'warning', 'title' => 'Rate must be greater than 0']);
            return;
        }

        $currencyService = app(CurrencyService::class);
        $currencyService->storeRate(
            $this->base_currency,
            $this->target_currency,
            $rate,
            $this->provider,
            auth()->user()->tenant_id
        );

        $this->rate_value = '';
        $this->editingId = null;
        $this->loadRates();

        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Exchange rate saved']);
    }

    public function editRate(string $id): void
    {
        $rate = ExchangeRate::find($id);
        if ($rate) {
            $this->editingId = $id;
            $this->base_currency = $rate->base_currency;
            $this->target_currency = $rate->target_currency;
            $this->rate_value = (string) $rate->rate;
            $this->provider = $rate->provider;
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->rate_value = '';
    }

    public function deleteRate(string $id): void
    {
        ExchangeRate::find($id)?->delete();
        $this->loadRates();
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Exchange rate deleted']);
    }
}
