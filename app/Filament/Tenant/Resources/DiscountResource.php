<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Discount;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Discount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.configuration');
    }

    protected static ?int $navigationSort = 40;


    public static function getNavigationLabel(): string
    {
        return __('navigation.discounts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Discount Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Summer Sale, Loyalty Discount'),

                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'percentage' => 'Percentage (%)',
                                        'fixed_amount' => 'Fixed Amount (UGX)',
                                    ])
                                    ->live(),

                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label(fn ($get) => $get('type') === 'percentage' ? 'Percentage' : 'Amount (UGX)')
                                    ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : 'UGX'),
                            ]),

                        Forms\Components\TextInput::make('minimum_purchase')
                            ->numeric()
                            ->minValue(0)
                            ->label('Minimum Purchase (UGX)')
                            ->helperText('Optional: Discount applies only for purchases above this amount'),

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

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn(string $state): string => $state === 'percentage' ? 'Percentage' : 'Fixed Amount')
                    ->colors([
                        'blue' => 'percentage',
                        'green' => 'fixed_amount',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn($state, Discount $record) => 
                        $record->type === 'percentage' ? "{$state}%" : "UGX {$state}"
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_purchase')
                    ->formatStateUsing(fn($state) => $state ? "UGX {$state}" : '-')
                    ->label('Min Purchase'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
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
            'index' => \App\Filament\Tenant\Resources\DiscountResource\Pages\ListDiscounts::route('/'),
            'create' => \App\Filament\Tenant\Resources\DiscountResource\Pages\CreateDiscount::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\DiscountResource\Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
