<?php

namespace App\Filament\Tenant\Resources\StockMovements\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['product', 'supplier', 'user']))
            ->columns([
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'warning',
                        'adjustment' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('supplier.company_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('movement_date', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/tenant/stock-movements/' . $record->id)
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
