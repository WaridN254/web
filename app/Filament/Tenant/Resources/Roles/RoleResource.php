<?php

namespace App\Filament\Tenant\Resources\Roles;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Roles\Pages\CreateRole;
use App\Filament\Tenant\Resources\Roles\Pages\EditRole;
use App\Filament\Tenant\Resources\Roles\Pages\ListRoles;
use App\Filament\Tenant\Resources\Roles\Schemas\RoleForm;
use App\Filament\Tenant\Resources\Roles\Tables\RolesTable;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Role::class;

    protected static ?int $navigationSort = 14;

    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.administration');
    }


    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
