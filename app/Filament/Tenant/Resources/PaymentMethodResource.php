<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    use HasPermission;
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.configuration');
    }

    protected static ?int $navigationSort = 35;


    public static function getNavigationLabel(): string
    {
        return __('navigation.payment_methods');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Payment Method Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. MTN MoMo'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'mobile' => 'Mobile Money',
                                'bank' => 'Bank Transfer',
                                'credit' => 'Credit',
                            ]),

                        Forms\Components\Select::make('icon_name')
                            ->required()
                            ->options([
                                'cash' => '💰 Cash',
                                'credit-card' => '💳 Credit Card',
                                'mobile' => '📱 Mobile Money',
                                'bank' => '🏦 Bank',
                                'wallet' => '👛 Wallet',
                                'check' => '✓ Check',
                                'gift' => '🎁 Gift Card',
                            ])
                            ->label('Icon'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order'),
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
                        'blue' => 'cash',
                        'green' => 'card',
                        'orange' => 'mobile',
                        'purple' => 'bank',
                        'gray' => 'credit',
                    ]),

                Tables\Columns\TextColumn::make('icon_name'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
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
            ])
            ->defaultSort('sort_order');
    }



    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Tenant\Resources\PaymentMethodResource\Pages\ListPaymentMethods::route('/'),
            'create' => \App\Filament\Tenant\Resources\PaymentMethodResource\Pages\CreatePaymentMethod::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\PaymentMethodResource\Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
