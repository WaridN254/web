<?php

namespace App\Filament\Tenant\Resources\Warranties;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Warranties\Pages\CreateWarranty;
use App\Filament\Tenant\Resources\Warranties\Pages\EditWarranty;
use App\Filament\Tenant\Resources\Warranties\Pages\ListWarranties;
use App\Filament\Tenant\Resources\Warranties\Schemas\WarrantyForm;
use App\Filament\Tenant\Resources\Warranties\Tables\WarrantiesTable;
use App\Models\ProductWarranty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WarrantyResource extends Resource
{
    use HasPermission;
    protected static ?string $model = ProductWarranty::class;

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }


    protected static ?string $recordTitleAttribute = 'serial_number';


    public static function getNavigationLabel(): string
    {
        return __('navigation.variants');
    }


    protected static ?string $modelLabel = 'Warranty';

    public static function form(Schema $schema): Schema
    {
        return WarrantyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarrantiesTable::configure($table);
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
            'index' => ListWarranties::route('/'),
            'create' => CreateWarranty::route('/create'),
            'edit' => EditWarranty::route('/{record}/edit'),
        ];
    }
}
