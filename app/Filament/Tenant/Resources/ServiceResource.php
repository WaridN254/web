<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Service;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.catalog');
    }

    protected static ?int $navigationSort = 15;


    public static function getNavigationLabel(): string
    {
        return __('navigation.services');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Service Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Haircut'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'drinks' => 'Drinks',
                                'food' => 'Food',
                                'salon' => 'Salon',
                                'repair' => 'Repair',
                                'consulting' => 'Consulting',
                                'other' => 'Other',
                            ]),

                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('UGX'),

                                Forms\Components\TextInput::make('duration_minutes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->label('Duration (minutes)')
                                    ->default(30),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->placeholder('Optional description...'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Status'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'blue' => 'drinks',
                        'green' => 'food',
                        'pink' => 'salon',
                        'orange' => 'repair',
                        'purple' => 'consulting',
                    ]),

                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn($state) => "UGX {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->formatStateUsing(fn($state) => $state ? "{$state} min" : '-')
                    ->label('Duration'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->slideOver(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Tenant\Resources\ServiceResource\Pages\ListServices::route('/'),
            'create' => \App\Filament\Tenant\Resources\ServiceResource\Pages\CreateService::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\ServiceResource\Pages\EditService::route('/{record}/edit'),
        ];
    }
}
