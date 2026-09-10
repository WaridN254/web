<?php

namespace App\Filament\Tenant\Resources\TaxCategoryResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\TaxCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxCategory extends CreateRecord
{
    use HasPermission;
    protected static string $resource = TaxCategoryResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
