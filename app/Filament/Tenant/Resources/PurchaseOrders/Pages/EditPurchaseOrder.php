<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    use HasPermission;
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => !in_array($this->record->status, ['approved', 'rejected'])),
        ];
    }

    protected function beforeFill(): void
    {
        // Redirect approved/rejected orders to the view page
        if (in_array($this->record->status, ['approved', 'rejected'])) {
            $this->redirect(PurchaseOrderResource::getUrl('view', ['record' => $this->record]));
        }
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
