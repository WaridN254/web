<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockTransfers\StockTransferResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewStockTransfer extends ViewRecord
{
    use HasPermission;

    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }),

            Action::make('ship')
                ->label('Ship')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->visible(fn ($record) => $record->status === 'approved')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'in_transit',
                        'shipped_at' => now(),
                    ]);
                }),

            Action::make('receive')
                ->label('Receive')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'in_transit')
                ->requiresConfirmation()
                ->action(function ($record) {
                    // Update stock for each item
                    foreach ($record->items as $item) {
                        // Create stock movements
                        \App\Models\StockMovement::create([
                            'tenant_id' => $record->tenant_id,
                            'branch_id' => $record->from_branch_id,
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                            'movement_type' => 'out',
                            'quantity' => $item->quantity,
                            'reference_id' => $record->id,
                            'reason' => 'Stock transfer out - ' . $record->transfer_number,
                            'movement_date' => now(),
                            'user_id' => auth()->id(),
                        ]);

                        \App\Models\StockMovement::create([
                            'tenant_id' => $record->tenant_id,
                            'branch_id' => $record->to_branch_id,
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                            'movement_type' => 'in',
                            'quantity' => $item->quantity,
                            'reference_id' => $record->id,
                            'reason' => 'Stock transfer in - ' . $record->transfer_number,
                            'movement_date' => now(),
                            'user_id' => auth()->id(),
                        ]);
                    }

                    $record->update([
                        'status' => 'received',
                        'received_by' => auth()->id(),
                        'received_at' => now(),
                    ]);
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'pending']))
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                }),
        ];
    }
}
