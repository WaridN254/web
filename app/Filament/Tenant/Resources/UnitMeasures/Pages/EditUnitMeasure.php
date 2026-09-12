<?php

namespace App\Filament\Tenant\Resources\UnitMeasures\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\UnitMeasures\UnitMeasureResource;
use Filament\Resources\Pages\EditRecord;

class EditUnitMeasure extends EditRecord
{
    use HasPermission;
    protected static string $resource = UnitMeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
