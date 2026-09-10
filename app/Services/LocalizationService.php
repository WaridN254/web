<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class LocalizationService
{
    public function resolveLanguage(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if ($user && $user->language) {
            return $user->language;
        }

        $tenant = $user?->tenant;
        if ($tenant && $tenant->default_language) {
            return $tenant->default_language;
        }

        return config('app.locale', 'en');
    }

    public function resolveLocale(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if ($user && $user->locale) {
            return $user->locale;
        }

        $tenant = $user?->tenant;
        if ($tenant && $tenant->default_locale) {
            return $tenant->default_locale;
        }

        return config('app.locale', 'en');
    }

    public function resolveTimezone(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if ($user && $user->timezone) {
            return $user->timezone;
        }

        $tenant = $user?->tenant;
        if ($tenant && $tenant->timezone) {
            return $tenant->timezone;
        }

        return config('app.timezone', 'UTC');
    }

    public function resolveCurrency(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        $tenant = $user?->tenant;

        if ($tenant && $tenant->default_currency) {
            return $tenant->default_currency;
        }

        return 'USD';
    }

    public function setLocale(string $locale): void
    {
        App::setLocale($locale);
        Session::put('locale', $locale);
    }

    public function getDirection(?User $user = null): string
    {
        $lang = $this->resolveLanguage($user);
        $language = Language::where('code', $lang)->first();

        return $language?->direction ?? 'ltr';
    }

    public function getDateFormat(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        $tenant = $user?->tenant;

        return $tenant?->date_format ?? 'd/m/Y';
    }

    public function getTimeFormat(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        $tenant = $user?->tenant;

        return $tenant?->time_format ?? '24';
    }

    public function formatDate(\DateTimeInterface $date, ?string $format = null, ?User $user = null): string
    {
        $format = $format ?? $this->getDateFormat($user);
        $timezone = $this->resolveTimezone($user);

        return $date->setTimezone(new \DateTimeZone($timezone))->format($format);
    }

    public function formatTime(\DateTimeInterface $date, ?User $user = null): string
    {
        $format = $this->getTimeFormat($user) === '24' ? 'H:i' : 'g:i A';
        $timezone = $this->resolveTimezone($user);

        return $date->setTimezone(new \DateTimeZone($timezone))->format($format);
    }

    public function formatNumber(float $number, int $decimals = 2, ?User $user = null): string
    {
        $currencyCode = $this->resolveCurrency($user);
        $currencyService = app(CurrencyService::class);
        $currency = $currencyService->getByCode($currencyCode);

        if ($currency) {
            $decimals = $currency->decimal_places;
        }

        return number_format(
            $number,
            $decimals,
            $currency?->decimal_separator ?? '.',
            $currency?->thousands_separator ?? ','
        );
    }

    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->resolveLanguage();
        $fallback = config('app.fallback_locale', 'en');

        $translated = $this->getTranslation($key, $locale, $replace);

        if ($translated === $key && $locale !== $fallback) {
            $translated = $this->getTranslation($key, $fallback, $replace);
        }

        return $translated;
    }

    private function getTranslation(string $key, string $locale, array $replace = []): string
    {
        $path = resource_path("lang/{$locale}/" . str_replace('.', '/', $key) . '.php');

        if (!file_exists($path)) {
            $path = resource_path("lang/{$locale}/" . str_replace('.', '/', $key) . '.json');
        }

        if (!file_exists($path)) {
            return $key;
        }

        $translations = Cache::remember(
            "translation_{$locale}_{$key}",
            3600,
            fn () => require $path
        );

        $message = is_array($translations) ? ($translations['*'] ?? ($translations[$key] ?? $key)) : $translations;

        if (!empty($replace)) {
            foreach ($replace as $search => $replaceValue) {
                $message = str_replace(":{$search}", $replaceValue, $message);
            }
        }

        return $message;
    }
}
