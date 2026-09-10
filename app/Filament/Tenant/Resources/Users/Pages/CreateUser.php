<?php

namespace App\Filament\Tenant\Resources\Users\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasPermission;
    protected static string $resource = UserResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
