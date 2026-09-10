<?php

namespace App\Filament\Tenant\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('total_orders')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_spent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit_limit')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('credit_enabled')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_deleted')
                    ->boolean(),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->searchable(),
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
