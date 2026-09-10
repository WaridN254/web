<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function getCurrencyMeta(string $code): ?Currency
    {
        return Cache::remember(
            "currency_meta_{$code}",
            86400,
            fn () => Currency::where('code', strtoupper($code))->first()
        );
    }

    public function getByCode(string $code): ?Currency
    {
        return $this->getCurrencyMeta($code);
    }

    public function getActiveCurrencies()
    {
        return Cache::remember(
            'active_currencies',
            86400,
            fn () => Currency::where('is_active', true)->orderBy('name')->get()
        );
    }

    public function getAllCurrencies()
    {
        return Cache::remember(
            'all_currencies',
            86400,
            fn () => Currency::orderBy('name')->get()
        );
    }

    public function format(float $amount, string $currencyCode, ?string $locale = null): string
    {
        $currency = $this->getCurrencyMeta($currencyCode);

        if (!$currency) {
            return number_format($amount, 2, '.', ',');
        }

        $formatted = number_format(
            $amount,
            $currency->decimal_places,
            $currency->decimal_separator,
            $currency->thousands_separator
        );

        if ($currency->symbol_first === 'before') {
            return $currency->symbol . $formatted;
        }

        return $formatted . ' ' . $currency->symbol;
    }

    public function formatRaw(float $amount, string $currencyCode): string
    {
        $currency = $this->getCurrencyMeta($currencyCode);

        if (!$currency) {
            return number_format($amount, 2, '.', ',');
        }

        return number_format(
            $amount,
            $currency->decimal_places,
            $currency->decimal_separator,
            $currency->thousands_separator
        );
    }

    public function getDecimalPlaces(string $currencyCode): int
    {
        $currency = $this->getCurrencyMeta($currencyCode);

        return $currency?->decimal_places ?? 2;
    }

    public function getSymbol(string $currencyCode): string
    {
        $currency = $this->getCurrencyMeta($currencyCode);

        return $currency?->symbol ?? $currencyCode;
    }

    public function validate(string $currencyCode): bool
    {
        return Currency::where('code', strtoupper($currencyCode))->where('is_active', true)->exists();
    }

    public function convert(float $amount, string $from, string $to, ?string $tenantId = null): array
    {
        if (strtoupper($from) === strtoupper($to)) {
            return [
                'amount' => $amount,
                'rate' => 1.0,
                'from' => strtoupper($from),
                'to' => strtoupper($to),
                'effective_at' => now(),
            ];
        }

        $rate = $this->getExchangeRate($from, $to, $tenantId);

        if ($rate === null) {
            return [
                'amount' => $amount,
                'rate' => null,
                'from' => strtoupper($from),
                'to' => strtoupper($to),
                'effective_at' => null,
                'error' => 'No exchange rate available',
            ];
        }

        return [
            'amount' => round($amount * $rate->rate, $this->getDecimalPlaces($to)),
            'rate' => $rate->rate,
            'from' => strtoupper($from),
            'to' => strtoupper($to),
            'effective_at' => $rate->effective_at,
            'provider' => $rate->provider,
        ];
    }

    public function getExchangeRate(string $from, string $to, ?string $tenantId = null): ?ExchangeRate
    {
        return ExchangeRate::where('base_currency', strtoupper($from))
            ->where('target_currency', strtoupper($to))
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereNull('tenant_id');
            })
            ->orderByDesc('effective_at')
            ->first();
    }

    public function storeRate(string $from, string $to, float $rate, string $provider = 'manual', ?string $tenantId = null): ExchangeRate
    {
        return ExchangeRate::create([
            'tenant_id' => $tenantId,
            'base_currency' => strtoupper($from),
            'target_currency' => strtoupper($to),
            'rate' => $rate,
            'provider' => $provider,
            'effective_at' => now(),
        ]);
    }

    public function getTenantCurrency(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        $tenant = $user?->tenant;

        return $tenant?->default_currency ?? 'USD';
    }

    public function clearCache(): void
    {
        Cache::forget('active_currencies');
        Cache::forget('all_currencies');

        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            Cache::forget("currency_meta_{$currency->code}");
        }
    }
}
