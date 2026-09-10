<?php

namespace App\Filament\Tenant\Resources\StockMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'company_name')
                    ->searchable(),
                Select::make('movement_type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'adjustment' => 'Adjustment',
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                DateTimePicker::make('movement_date')
                    ->default(now())
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'full_name')
                    ->default(fn () => auth()->id())
                    ->required(),
            ]);
    }
}
