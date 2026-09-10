<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\UnitMeasure;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class UnitsPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?int $navigationSort = 47;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.units');
    }


    protected static ?string $title = 'Product units';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.tenant.pages.units-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(UnitMeasure::query())
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('abbreviation')->label('Abbreviation')->searchable()->sortable(),
                TextColumn::make('sort_order')->label('Sort Order')->numeric()->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean()->sortable(),
            ])
            ->searchPlaceholder('Search units');
    }
}
