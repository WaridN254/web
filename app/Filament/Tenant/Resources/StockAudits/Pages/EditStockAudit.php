<?php

namespace App\Filament\Tenant\Resources\StockAudits\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockAudits\StockAuditResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockAudit extends EditRecord
{
    use HasPermission;
    protected static string $resource = StockAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('finalizeInventory')
                ->label('Finalize Inventory')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Finalize Inventory Audit')
                ->modalDescription('Are you sure you want to finalize this audit? This will update your system stock for all items with physical counts, creating adjustment records.')
                ->visible(fn (\App\Models\StockAudit $record) => $record->status !== 'finalized')
                ->action(function (\App\Models\StockAudit $record) {
                    $tenantId = auth()->user()->tenant_id;
                    $userId = auth()->id();
                    $now = now();

                    // Loop through all items and update stock if discrepancy exists
                    foreach ($record->items as $item) {
                        // In UI, discrepancy = physical_qty - system_qty.
                        // Wait, let's recalculate it just to be sure.
                        $diff = floatval($item->physical_qty) - floatval($item->system_qty);

                        if ($diff !== 0.0) {
                            // Update discrepancy on the item
                            $item->update(['discrepancy' => $diff]);

                            // Create stock movement (adjustment)
                            \App\Models\StockMovement::create([
                                'tenant_id' => $tenantId,
                                'branch_id' => $record->branch_id,
                                'product_id' => $item->product_id,
                                'movement_type' => 'adjustment',
                                'quantity' => $diff,
                                'reason' => 'Inventory Audit Finalization',
                                'reference_id' => 'Audit ' . $record->id,
                                'user_id' => $userId,
                                'movement_date' => $now,
                            ]);
                        }
                    }

                    // Update Audit status
                    $record->update([
                        'status' => 'finalized',
                        'end_date' => $now,
                    ]);

                    $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Inventory Audit Finalized successfully!']);
                        
                    return redirect(\App\Filament\Tenant\Resources\StockAudits\StockAuditResource::getUrl('index'));
                }),
            DeleteAction::make()
                ->visible(fn (\App\Models\StockAudit $record) => $record->status !== 'finalized'),
        ];
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
