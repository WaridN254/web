<?php

namespace App\Filament\Tenant\Resources\UnitMeasures;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\UnitMeasures\Pages\CreateUnitMeasure;
use App\Filament\Tenant\Resources\UnitMeasures\Pages\EditUnitMeasure;
use App\Filament\Tenant\Resources\UnitMeasures\Pages\ListUnitMeasures;
use App\Filament\Tenant\Resources\UnitMeasures\Schemas\UnitMeasureForm;
use App\Filament\Tenant\Resources\UnitMeasures\Tables\UnitMeasuresTable;
use App\Models\UnitMeasure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UnitMeasureResource extends Resource
{
    use HasPermission;
    protected static ?string $model = UnitMeasure::class;

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }


    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('navigation.units');
    }


    public static function form(Schema $schema): Schema
    {
        return UnitMeasureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitMeasuresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListUnitMeasures::route('/'),
            'create' => CreateUnitMeasure::route('/create'),
            'edit' => EditUnitMeasure::route('/{record}/edit'),
        ];
    }
}
