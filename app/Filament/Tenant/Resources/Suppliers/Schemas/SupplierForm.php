<?php

namespace App\Filament\Tenant\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('contact_person')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('tin')
                    ->label('TIN')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('active')
                    ->columnSpanFull(),
            ]);
    }
}
