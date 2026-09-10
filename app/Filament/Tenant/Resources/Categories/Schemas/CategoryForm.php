<?php

namespace App\Filament\Tenant\Resources\Categories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('icon_name')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_deleted')
                    ->required(),
                Textarea::make('sync_status')
                    ->required()
                    ->default('pending')
                    ->columnSpanFull(),
                DateTimePicker::make('last_synced_at'),

            ]);
    }
}
