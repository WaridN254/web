<?php

namespace App\Filament\Tenant\Resources\Branches\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Branches\BranchResource;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    use HasPermission;
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
