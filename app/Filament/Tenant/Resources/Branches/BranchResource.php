<?php

namespace App\Filament\Tenant\Resources\Branches;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Branches\Pages\CreateBranch;
use App\Filament\Tenant\Resources\Branches\Pages\EditBranch;
use App\Filament\Tenant\Resources\Branches\Pages\ListBranches;
use App\Filament\Tenant\Resources\Branches\Schemas\BranchForm;
use App\Filament\Tenant\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Branch::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.branch_management');
    }


    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('navigation.branches');
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
