<?php

namespace App\Filament\Tenant\Resources\Branches\Schemas;

use App\Models\Business;
use App\Models\User;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Branch name')
                            ->columnSpan(1),
                        TextInput::make('code')
                            ->maxLength(10)
                            ->placeholder('e.g. KLA')
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Branch phone number'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Branch email'),
                        TextInput::make('address')
                            ->maxLength(500)
                            ->placeholder('Branch address')
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('city')
                                ->maxLength(255)
                                ->placeholder('City'),
                            TextInput::make('country')
                                ->maxLength(255)
                                ->placeholder('Country'),
                        ]),
                    ]),

                Section::make('Configuration')
                    ->columns(2)
                    ->schema([
                        Select::make('manager_id')
                            ->relationship('manager', 'full_name', fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select manager')
                            ->label('Manager'),
                        Select::make('currency_code')
                            ->options(fn () => \App\Models\Currency::pluck('code', 'code'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select currency')
                            ->label('Currency'),
                        Select::make('timezone')
                            ->options(fn () => collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray())
                            ->searchable()
                            ->preload()
                            ->placeholder('Select timezone')
                            ->label('Timezone'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                        Toggle::make('is_default')
                            ->default(false)
                            ->label('Default Branch'),
                    ]),

                Section::make('User Assignment')
                    ->schema([
                        Select::make('users')
                            ->relationship('users', 'full_name', fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Assign users to this branch')
                            ->label('Branch Users'),
                    ]),
            ]);
    }
}
