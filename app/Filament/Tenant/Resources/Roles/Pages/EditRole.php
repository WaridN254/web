<?php

namespace App\Filament\Tenant\Resources\Roles\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use HasPermission;
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
