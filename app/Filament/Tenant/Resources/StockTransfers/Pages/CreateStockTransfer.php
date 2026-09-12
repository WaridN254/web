<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockTransfer extends CreateRecord
{
    use HasPermission;

    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        $data['requested_at'] = now();
        $data['branch_id'] = app(\App\Services\BranchService::class)->getActiveBranchId();

        return $data;
    }


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
