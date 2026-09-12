<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CurrencyService;
use App\Services\LocalizationService;
use Filament\Pages\Page;

class LocalizationSettingsPage extends Page
{
    use HasPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings');
    }

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('navigation.localization');
    }

    protected static ?string $slug = 'localization-settings';

    public string $view = 'filament.tenant.pages.localization-settings';

    public string $default_language = 'en';
    public string $default_locale = 'en';
    public string $default_currency = 'UGX';
    public string $timezone = 'UTC';
    public string $date_format = 'd/m/Y';
    public string $time_format = '24';

    public array $languages = [];
    public array $currencies = [];
    public array $timezones = [];
    public array $dateFormats = [];
    public array $preview = [];

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        if ($tenant) {
            $this->default_language = $tenant->default_language ?? 'en';
            $this->default_locale = $tenant->default_locale ?? 'en';
            $this->default_currency = $tenant->default_currency ?? 'UGX';
            $this->timezone = $tenant->timezone ?? 'UTC';
            $this->date_format = $tenant->date_format ?? 'd/m/Y';
            $this->time_format = $tenant->time_format ?? '24';
        }

        $this->languages = Language::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($l) => is_dir(resource_path('lang/' . $l->code)) || is_dir(base_path('lang/' . $l->code)))
            ->values()
            ->toArray();
        $this->currencies = Currency::where('is_active', true)->orderBy('name')->get()->toArray();
        $this->timezones = $this->getTimezones();
        $this->dateFormats = $this->getDateFormats();
        $this->updatePreview();
    }

    public function save(): void
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            $this$this->dispatch('gooey-toast', ['type' => 'error', 'title' => 'No tenant found']);
            return;
        }

        $tenant->update([
            'default_language' => $this->default_language,
            'default_locale' => $this->default_locale,
            'default_currency' => $this->default_currency,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
        ]);

        app(LocalizationService::class)->setLocale($this->default_language);
        app(CurrencyService::class)->clearCache();

        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Localization settings saved']);
    }

    public function updatedDefaultLanguage(): void
    {
        $this->updatePreview();
    }

    public function updatedDefaultCurrency(): void
    {
        $this->updatePreview();
    }

    public function updatedDateFormat(): void
    {
        $this->updatePreview();
    }

    public function updatedTimeFormat(): void
    {
        $this->updatePreview();
    }

    public function updatedTimezone(): void
    {
        $this->updatePreview();
    }

    public function updatePreview(): void
    {
        $now = now();
        if ($this->timezone) {
            $now->setTimezone(new \DateTimeZone($this->timezone));
        }

        $this->preview['date'] = $now->format($this->date_format);
        $this->preview['time'] = $this->time_format === '24' ? $now->format('H:i') : $now->format('g:i A');
        $this->preview['number'] = '1234567.89';

        $currencyService = app(CurrencyService::class);
        $this->preview['amount'] = $currencyService->format(1250000, $this->default_currency);

        $lang = Language::where('code', $this->default_language)->first();
        $this->preview['direction'] = $lang?->direction ?? 'ltr';
        $this->preview['language_name'] = $lang?->name ?? 'English';
    }

    public function setDefaultLanguage(string $code): void
    {
        $this->default_language = $code;
        $lang = Language::where('code', $code)->first();
        if ($lang) {
            $this->default_locale = $lang->locale ?? $code;
        }
        $this->updatePreview();
    }

    public function setDefaultCurrency(string $code): void
    {
        $this->default_currency = $code;
        $this->updatePreview();
    }

    private function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'Africa/Kampala' => 'East Africa Time (EAT)',
            'Africa/Nairobi' => 'East Africa Time (EAT)',
            'Africa/Dar_es_Salaam' => 'East Africa Time (EAT)',
            'Africa/Lagos' => 'West Africa Time (WAT)',
            'Africa/Accra' => 'Greenwich Mean Time (GMT)',
            'Africa/Johannesburg' => 'South Africa Standard Time (SAST)',
            'Africa/Cairo' => 'Eastern European Time (EET)',
            'Africa/Addis_Ababa' => 'East Africa Time (EAT)',
            'Europe/London' => 'Greenwich Mean Time (GMT)',
            'Europe/Paris' => 'Central European Time (CET)',
            'Europe/Berlin' => 'Central European Time (CET)',
            'Europe/Moscow' => 'Moscow Time (MSK)',
            'America/New_York' => 'Eastern Time (ET)',
            'America/Chicago' => 'Central Time (CT)',
            'America/Denver' => 'Mountain Time (MT)',
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'Asia/Dubai' => 'Gulf Standard Time (GST)',
            'Asia/Riyadh' => 'Arabia Standard Time (AST)',
            'Asia/Kolkata' => 'India Standard Time (IST)',
            'Asia/Shanghai' => 'China Standard Time (CST)',
            'Asia/Tokyo' => 'Japan Standard Time (JST)',
            'Asia/Seoul' => 'Korea Standard Time (KST)',
            'Australia/Sydney' => 'Australian Eastern Time (AET)',
        ];
    }

    private function getDateFormats(): array
    {
        return [
            'd/m/Y' => 'DD/MM/YYYY',
            'm/d/Y' => 'MM/DD/YYYY',
            'Y-m-d' => 'YYYY-MM-DD',
            'd-m-Y' => 'DD-MM-YYYY',
            'd.M.Y' => 'DD.Mon.YYYY',
            'd F Y' => 'DD Month YYYY',
        ];
    }
}
