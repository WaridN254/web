<?php

namespace App\Filament\Tenant\Resources\StockMovements\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    use HasPermission;
    protected static string $resource = StockMovementResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
