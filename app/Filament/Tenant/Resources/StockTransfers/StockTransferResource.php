<?php

namespace App\Filament\Tenant\Resources\StockTransfers;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockTransfers\Pages\CreateStockTransfer;
use App\Filament\Tenant\Resources\StockTransfers\Pages\EditStockTransfer;
use App\Filament\Tenant\Resources\StockTransfers\Pages\ListStockTransfers;
use App\Filament\Tenant\Resources\StockTransfers\Pages\ViewStockTransfer;
use App\Filament\Tenant\Resources\StockTransfers\Schemas\StockTransferForm;
use App\Filament\Tenant\Resources\StockTransfers\Tables\StockTransfersTable;
use App\Models\StockTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockTransferResource extends Resource
{
    use HasPermission;

    protected static ?string $model = StockTransfer::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.stock');
    }

    protected static ?string $recordTitleAttribute = 'transfer_number';

    public static function getNavigationLabel(): string
    {
        return __('navigation.stock_transfers');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['fromBranch', 'toBranch', 'items.product'])
            ->latest();
    }

    public static function form(Schema $schema): Schema
    {
        return StockTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockTransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListStockTransfers::route('/'),
            'create' => CreateStockTransfer::route('/create'),
            'view' => ViewStockTransfer::route('/{record}'),
            'edit' => EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
