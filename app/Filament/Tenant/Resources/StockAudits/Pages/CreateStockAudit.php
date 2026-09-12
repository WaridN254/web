<?php

namespace App\Filament\Tenant\Resources\StockAudits\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockAudits\StockAuditResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockAudit extends CreateRecord
{
    use HasPermission;
    protected static string $resource = StockAuditResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
