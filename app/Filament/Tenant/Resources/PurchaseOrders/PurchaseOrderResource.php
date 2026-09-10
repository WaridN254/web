<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Tenant\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\Tenant\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Tenant\Resources\PurchaseOrders\Tables\PurchaseOrdersTable;
use App\Models\PurchaseOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    use HasPermission;
    protected static ?string $model = PurchaseOrder::class;

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.purchasing');
    }


    protected static ?string $recordTitleAttribute = 'purchase_number';

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrdersTable::configure($table);
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
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'view' => ViewPurchaseOrder::route('/{record}'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
