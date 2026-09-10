<?php

namespace App\Filament\Tenant\Resources\ComplianceReminderResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ComplianceReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplianceReminders extends ListRecords
{
    use HasPermission;
    protected static string $resource = ComplianceReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
