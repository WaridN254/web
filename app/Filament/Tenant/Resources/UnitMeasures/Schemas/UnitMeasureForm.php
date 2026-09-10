<?php

namespace App\Filament\Tenant\Resources\UnitMeasures\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitMeasureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('abbreviation')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sync_status')
                    ->default('pending')
                    ->maxLength(50),
                DateTimePicker::make('last_synced_at')
                    ->label('Last synced at'),
            ]);
    }
}
