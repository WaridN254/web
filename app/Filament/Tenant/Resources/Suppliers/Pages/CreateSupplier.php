<?php

namespace App\Filament\Tenant\Resources\Suppliers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    use HasPermission;
    protected static string $resource = SupplierResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
