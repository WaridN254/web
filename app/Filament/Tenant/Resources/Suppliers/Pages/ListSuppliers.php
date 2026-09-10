<?php

namespace App\Filament\Tenant\Resources\Suppliers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Suppliers\SupplierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    use HasPermission;
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
