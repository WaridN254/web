<?php

namespace App\Filament\Tenant\Resources\ProductBrands;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\ProductBrands\Pages\CreateProductBrand;
use App\Filament\Tenant\Resources\ProductBrands\Pages\EditProductBrand;
use App\Filament\Tenant\Resources\ProductBrands\Pages\ListProductBrands;
use App\Filament\Tenant\Resources\ProductBrands\Schemas\ProductBrandForm;
use App\Filament\Tenant\Resources\ProductBrands\Tables\ProductBrandsTable;
use App\Models\ProductBrand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductBrandResource extends Resource
{
    use HasPermission;
    protected static ?string $model = ProductBrand::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }


    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('navigation.brands');
    }


    public static function form(Schema $schema): Schema
    {
        return ProductBrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductBrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListProductBrands::route('/'),
            'create' => CreateProductBrand::route('/create'),
            'edit' => EditProductBrand::route('/{record}/edit'),
        ];
    }
}
