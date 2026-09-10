<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{

    public static function getNavigationLabel(): string
    {
        return __('navigation.admin_dashboard');
    }


    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->can_access_admin_dashboard ?? true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}