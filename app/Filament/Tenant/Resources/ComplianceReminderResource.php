<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\ComplianceReminder;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ComplianceReminderResource extends Resource
{
    use HasPermission;
    protected static ?string $model = ComplianceReminder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.compliance');
    }

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Compliance Reminder')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Rent payment, URA filing'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'rent' => 'Rent Payment',
                                'ura' => 'URA Filing',
                                'supplier' => 'Supplier Payments',
                                'salary' => 'Staff Salaries',
                                'license' => 'Trading License Renewal',
                                'insurance' => 'Insurance',
                                'other' => 'Other',
                            ]),

                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('UGX')
                                    ->label('Amount'),

                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'overdue' => 'Overdue',
                                    ])
                                    ->default('pending'),
                            ]),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->required()
                            ->label('Due Date'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->placeholder('Additional notes...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'blue' => 'rent',
                        'orange' => 'ura',
                        'green' => 'supplier',
                        'purple' => 'salary',
                        'red' => 'license',
                    ]),

                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn($state) => $state ? "UGX {$state}" : '-')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'overdue',
                    ]),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('category'),
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
            ->defaultSort('due_date');
    }



    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Tenant\Resources\ComplianceReminderResource\Pages\ListComplianceReminders::route('/'),
            'create' => \App\Filament\Tenant\Resources\ComplianceReminderResource\Pages\CreateComplianceReminder::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\ComplianceReminderResource\Pages\EditComplianceReminder::route('/{record}/edit'),
        ];
    }
}
