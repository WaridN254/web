<?php

namespace App\Filament\Tenant\Resources\ProductBrands\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ProductBrands\ProductBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductBrands extends ListRecords
{
    use HasPermission;
    protected static string $resource = ProductBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
