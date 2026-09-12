<?php

namespace App\Filament\Tenant\Resources\ProductBrands\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ProductBrands\ProductBrandResource;
use Filament\Resources\Pages\EditRecord;

class EditProductBrand extends EditRecord
{
    use HasPermission;
    protected static string $resource = ProductBrandResource::class;

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
