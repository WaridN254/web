<?php

namespace App\Filament\Tenant\Resources\Customers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    use HasPermission;
    protected static string $resource = CustomerResource::class;

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
