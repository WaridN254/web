<?php

namespace App\Filament\Tenant\Resources\UnitMeasures\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\UnitMeasures\UnitMeasureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUnitMeasure extends CreateRecord
{
    use HasPermission;
    protected static string $resource = UnitMeasureResource::class;

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
