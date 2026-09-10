<?php

namespace App\Filament\Tenant\Resources\DiscountResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    use HasPermission;
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
