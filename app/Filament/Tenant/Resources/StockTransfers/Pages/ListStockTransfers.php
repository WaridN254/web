<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockTransfers extends ListRecords
{
    use HasPermission;

    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
