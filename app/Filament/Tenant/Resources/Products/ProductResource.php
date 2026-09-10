<?php

namespace App\Filament\Tenant\Resources\Products;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Products\Pages\CreateProduct;
use App\Filament\Tenant\Resources\Products\Pages\EditProduct;
use App\Filament\Tenant\Resources\Products\Pages\ListProducts;
use App\Filament\Tenant\Resources\Products\Pages\ViewProduct;
use App\Filament\Tenant\Resources\Products\Schemas\ProductForm;
use App\Filament\Tenant\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }


    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
            'view' => ViewProduct::route('/{record}'),
        ];
    }
}
