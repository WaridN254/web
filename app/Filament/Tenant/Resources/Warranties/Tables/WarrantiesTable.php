<?php

namespace App\Filament\Tenant\Resources\Warranties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarrantiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('serial_number')
                    ->label('Serial No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('warranty_end')
                    ->label('Expires On')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'voided' => 'danger',
                        'claimed' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
