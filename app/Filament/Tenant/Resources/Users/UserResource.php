<?php

namespace App\Filament\Tenant\Resources\Users;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Users\Pages\CreateUser;
use App\Filament\Tenant\Resources\Users\Pages\EditUser;
use App\Filament\Tenant\Resources\Users\Pages\ListUsers;
use App\Filament\Tenant\Resources\Users\Schemas\UserForm;
use App\Filament\Tenant\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    use HasPermission;
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 13;

    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.administration');
    }


    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
