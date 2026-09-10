<?php

namespace App\Filament\Tenant\Resources\StockAudits\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;

class StockAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('auditor'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('start_date')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('end_date')
                    ->label('Finalized At')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'warning',
                        'finalized' => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('auditor.name')
                    ->label('Auditor'),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/tenant/stock-audits/' . $record->id)
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
