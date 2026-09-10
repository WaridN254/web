<?php

namespace App\Filament\Tenant\Resources\Warranties\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class WarrantyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Warranty Details')->schema([
                    TextInput::make('product_name')
                        ->label('Product Name')
                        ->disabled()
                        ->required(),
                        
                    TextInput::make('serial_number')
                        ->label('Serial Number')
                        ->disabled()
                        ->required(),
                        
                    TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->disabled(),
                        
                    TextInput::make('receipt_number')
                        ->label('Receipt Number')
                        ->disabled(),
                ])->columns(2),

                Section::make('Coverage Period')->schema([
                    DatePicker::make('warranty_start')
                        ->label('Warranty Start Date')
                        ->required(),
                        
                    DatePicker::make('warranty_end')
                        ->label('Warranty End Date')
                        ->required(),
                        
                    DatePicker::make('guarantee_end')
                        ->label('Guarantee End Date'),
                        
                    TextInput::make('provider')
                        ->label('Warranty Provider')
                        ->maxLength(255),
                ])->columns(2),
                
                Section::make('Status & Notes')->schema([
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'voided' => 'Voided',
                            'claimed' => 'Claimed',
                        ])
                        ->required()
                        ->default('active'),
                        
                    Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
