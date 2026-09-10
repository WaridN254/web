<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\DocumentVault;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentVaultResource extends Resource
{
    use HasPermission;
    protected static ?string $model = DocumentVault::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.compliance');
    }

    protected static ?int $navigationSort = 51;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Document Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Document title'),

                        Forms\Components\Select::make('document_type')
                            ->required()
                            ->options([
                                'trading_license' => 'Trading License',
                                'tax_certificate' => 'Tax Certificate',
                                'insurance' => 'Insurance Certificate',
                                'lease' => 'Lease Agreement',
                                'contract' => 'Contract',
                                'permit' => 'Permit/Authorization',
                                'receipt' => 'Receipt/Invoice',
                                'other' => 'Other',
                            ])
                            ->label('Document Type'),

                        Forms\Components\FileUpload::make('file_path')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword'])
                            ->maxSize(10240) // 10MB
                            ->storeFileNamesIn('file_name')
                            ->label('Upload Document'),

                        Forms\Components\DateTimePicker::make('expiry_date')
                            ->label('Expiry Date (Optional)'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->placeholder('Additional notes or details...'),
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

                Tables\Columns\BadgeColumn::make('document_type')
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'blue' => 'trading_license',
                        'green' => 'tax_certificate',
                        'purple' => 'insurance',
                        'orange' => 'lease',
                        'red' => 'contract',
                    ]),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state 
                        ? ($state->isPast() ? '❌ Expired: ' : '✓ ') . $state->format('M d, Y')
                        : '∞ No expiry'
                    ),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Uploaded'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type'),
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
            'index' => \App\Filament\Tenant\Resources\DocumentVaultResource\Pages\ListDocumentVaults::route('/'),
            'create' => \App\Filament\Tenant\Resources\DocumentVaultResource\Pages\CreateDocumentVault::route('/create'),
            'edit' => \App\Filament\Tenant\Resources\DocumentVaultResource\Pages\EditDocumentVault::route('/{record}/edit'),
        ];
    }
}
