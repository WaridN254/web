<?php

namespace App\Filament\Tenant\Resources\StockAudits;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockAudits\Pages\CreateStockAudit;
use App\Filament\Tenant\Resources\StockAudits\Pages\EditStockAudit;
use App\Filament\Tenant\Resources\StockAudits\Pages\ListStockAudits;
use App\Filament\Tenant\Resources\StockAudits\Pages\ViewStockAudit;
use App\Filament\Tenant\Resources\StockAudits\Schemas\StockAuditForm;
use App\Filament\Tenant\Resources\StockAudits\Tables\StockAuditsTable;
use App\Models\StockAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockAuditResource extends Resource
{
    use HasPermission;
    protected static ?string $model = StockAudit::class;

    protected static ?int $navigationSort = 42;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.stock');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.stock_audits');
    }


    public static function form(Schema $schema): Schema
    {
        return StockAuditForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockAuditsTable::configure($table);
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
            'index' => ListStockAudits::route('/'),
            'create' => CreateStockAudit::route('/create'),
            'view' => ViewStockAudit::route('/{record}'),
            'edit' => EditStockAudit::route('/{record}/edit'),
        ];
    }
}
