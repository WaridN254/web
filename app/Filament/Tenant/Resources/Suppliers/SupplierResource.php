<?php

namespace App\Filament\Tenant\Resources\Suppliers;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Tenant\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Tenant\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Tenant\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Tenant\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Supplier::class;

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.purchasing');
    }


    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
