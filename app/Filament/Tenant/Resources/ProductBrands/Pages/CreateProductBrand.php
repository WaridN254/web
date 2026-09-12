<?php

namespace App\Filament\Tenant\Resources\ProductBrands\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ProductBrands\ProductBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductBrand extends CreateRecord
{
    use HasPermission;
    protected static string $resource = ProductBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
