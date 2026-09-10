<?php

namespace App\Filament\Tenant\Concerns;

use App\Filament\Tenant\PermissionMap;

trait HasPermission
{
    public static function canViewAny(): bool
    {
        return static::permissionAllowed();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return static::permissionAllowed();
    }

    public static function permissionAllowed(): bool
    {
        if (is_subclass_of(static::class, \Filament\Resources\Pages\Page::class)) {
            return static::getResource()::canViewAny();
        }

        $permission = PermissionMap::for(static::class);

        if (blank($permission)) {
            return true;
        }

        return (bool) (auth()->user()?->hasPermission($permission) ?? false);
    }
}
