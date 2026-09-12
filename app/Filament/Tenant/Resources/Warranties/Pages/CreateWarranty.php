<?php

namespace App\Filament\Tenant\Resources\Warranties\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Warranties\WarrantyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarranty extends CreateRecord
{
    use HasPermission;
    protected static string $resource = WarrantyResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
