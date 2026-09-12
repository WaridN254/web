<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Resources\Pages\EditRecord;

class EditStockTransfer extends EditRecord
{
    use HasPermission;

    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
