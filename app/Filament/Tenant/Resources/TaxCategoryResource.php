<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\TaxCategory;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TaxCategoryResource extends Resource
{
    use HasPermission;
    protected static ?string $model = TaxCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-percent-badge';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.configuration');
    }

    protected static ?int $navigationSort = 45;


    public static function getNavigationLabel(): string
    {
        return __('navigation.tax_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Tax Category Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Standard Rated, Zero Rated'),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('e.g. A, B, C, D'),

                        Forms\Components\TextInput::make('rate')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->label('Tax Rate %'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
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

                Tables\Columns\TextColumn::make('code')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate')
                    ->formatStateUsing(fn($state) => "{$state}%")
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->slideOver(),
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
            'index' => \App\Filament\Tenant\Resources\TaxCategoryResource\Pages\ListTaxCategories::route('/'),
            'create' => \App\Filament\Tenant\Resources\TaxCategoryResource\Pages\CreateTaxCategory::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\TaxCategoryResource\Pages\EditTaxCategory::route('/{record}/edit'),
        ];
    }
}
