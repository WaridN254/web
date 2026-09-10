<?php

namespace App\Filament\Tenant\Resources\Customers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use HasPermission;
    protected static string $resource = CustomerResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
