<?php

namespace App\Filament\Tenant\Resources\StockMovements\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    use HasPermission;
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
