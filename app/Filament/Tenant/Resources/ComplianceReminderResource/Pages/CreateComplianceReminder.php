<?php

namespace App\Filament\Tenant\Resources\ComplianceReminderResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ComplianceReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateComplianceReminder extends CreateRecord
{
    use HasPermission;
    protected static string $resource = ComplianceReminderResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
