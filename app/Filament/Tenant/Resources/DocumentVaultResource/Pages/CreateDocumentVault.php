<?php

namespace App\Filament\Tenant\Resources\DocumentVaultResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\DocumentVaultResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentVault extends CreateRecord
{
    use HasPermission;
    protected static string $resource = DocumentVaultResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
