<?php

namespace App\Filament\Tenant\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('manager'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('manager.full_name')
                    ->label('Manager')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned'),
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                TextColumn::make('branch_stocks_count')
                    ->counts('branchStocks')
                    ->label('Products')
                    ->sortable(),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('is_default', 'desc')
            ->filters([])
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
