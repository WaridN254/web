<?php

namespace App\Filament\Tenant\Resources\StockAudits\Schemas;

use Filament\Schemas\Schema;

class StockAuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Audit Details')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('status')
                            ->content(fn ($record) => $record?->status ?? 'New'),
                        \Filament\Forms\Components\Placeholder::make('start_date')
                            ->content(fn ($record) => $record?->start_date?->format('M d, Y H:i A') ?? '-'),
                    ])->columns(2),
                    
                \Filament\Schemas\Components\Section::make('Inventory Counts')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('product_name')
                                    ->label('Product')
                                    ->content(fn ($record) => $record?->product_name ?? 'Unknown'),
                                    
                                \Filament\Forms\Components\Placeholder::make('system_qty')
                                    ->label('Expected Stock')
                                    ->content(fn ($record) => $record?->system_qty ?? 0),
                                    
                                \Filament\Forms\Components\TextInput::make('physical_qty')
                                    ->label('Physical Count')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                                        // We could update the discrepancy here dynamically if needed
                                    }),
                            ])
                            ->columns(3)
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disableItemMovement(),
                    ]),
            ]);
    }
}
