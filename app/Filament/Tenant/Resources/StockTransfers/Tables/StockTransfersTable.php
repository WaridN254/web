<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['fromBranch', 'toBranch', 'requestedBy']))
            ->columns([
                TextColumn::make('transfer_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('fromBranch.name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toBranch.name')
                    ->label('To')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'info',
                        'in_transit' => 'primary',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
