<?php

namespace App\Filament\Tenant\Resources\ProductBrands\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductBrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Textarea::make('sync_status')
                    ->default('pending')
                    ->columnSpanFull(),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
