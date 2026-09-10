<?php

namespace App\Filament\Tenant\Resources\StockMovements;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Tenant\Resources\StockMovements\Pages\EditStockMovement;
use App\Filament\Tenant\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Tenant\Resources\StockMovements\Pages\ViewStockMovement;
use App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm;
use App\Filament\Tenant\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    use HasPermission;
    protected static ?string $model = StockMovement::class;

    protected static ?int $navigationSort = 40;

    protected static bool $shouldRegisterNavigation = false;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.stock');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.stock_ledger');
    }


    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'create' => CreateStockMovement::route('/create'),
            'view' => ViewStockMovement::route('/{record}'),
            'edit' => EditStockMovement::route('/{record}/edit'),
        ];
    }
}
