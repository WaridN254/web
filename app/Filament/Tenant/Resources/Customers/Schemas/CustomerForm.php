<?php

namespace App\Filament\Tenant\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('full_name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('phone')
                    ->columnSpanFull(),
                Textarea::make('email')
                    ->label('Email address')
                    ->columnSpanFull(),
                Textarea::make('tin')
                    ->columnSpanFull(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('total_orders')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_spent')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('credit_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('credit_enabled')
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
