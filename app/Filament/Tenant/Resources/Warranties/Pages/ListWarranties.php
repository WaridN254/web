<?php

namespace App\Filament\Tenant\Resources\Warranties\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Warranties\WarrantyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarranties extends ListRecords
{
    use HasPermission;
    protected static string $resource = WarrantyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
