<?php

namespace App\Filament\Tenant\Resources\UnitMeasures\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\UnitMeasures\UnitMeasureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnitMeasures extends ListRecords
{
    use HasPermission;
    protected static string $resource = UnitMeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
