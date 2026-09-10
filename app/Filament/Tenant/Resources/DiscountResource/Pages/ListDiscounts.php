<?php

namespace App\Filament\Tenant\Resources\DiscountResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    use HasPermission;
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
