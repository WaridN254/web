<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Language;
use App\Services\LocalizationService;
use Filament\Pages\Page;

class UserPreferencesPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings');
    }

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('navigation.my_preferences');
    }

    protected static ?string $slug = 'user-preferences';

    public string $view = 'filament.tenant.pages.user-preferences';

    public string $language = '';
    public string $timezone = '';
    public array $languages = [];
    public array $timezones = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->language = $user->language ?? auth()->user()->tenant->default_language ?? 'en';
        $this->timezone = $user->timezone ?? auth()->user()->tenant->timezone ?? 'UTC';
        $this->languages = Language::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($l) => is_dir(resource_path('lang/' . $l->code)) || is_dir(base_path('lang/' . $l->code)))
            ->values()
            ->toArray();
        $this->timezones = $this->getTimezones();
    }

    public function save(): void
    {
        $user = auth()->user();

        $user->forceFill([
            'language' => $this->language,
            'timezone' => $this->timezone,
        ])->save();

        app(LocalizationService::class)->setLocale($this->language);

        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Preferences saved']);
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
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'Asia/Dubai' => 'Gulf Standard Time (GST)',
            'Asia/Riyadh' => 'Arabia Standard Time (AST)',
            'Asia/Kolkata' => 'India Standard Time (IST)',
            'Asia/Shanghai' => 'China Standard Time (CST)',
            'Asia/Tokyo' => 'Japan Standard Time (JST)',
            'Asia/Seoul' => 'Korea Standard Time (KST)',
        ];
    }
}
