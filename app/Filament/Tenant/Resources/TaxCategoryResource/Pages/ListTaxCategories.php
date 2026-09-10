<?php

namespace App\Filament\Tenant\Resources\TaxCategoryResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\TaxCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxCategories extends ListRecords
{
    use HasPermission;
    protected static string $resource = TaxCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
