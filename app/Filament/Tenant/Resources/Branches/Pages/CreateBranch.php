<?php

namespace App\Filament\Tenant\Resources\Branches\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    use HasPermission;
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
