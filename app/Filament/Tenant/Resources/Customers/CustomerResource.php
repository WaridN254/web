<?php

namespace App\Filament\Tenant\Resources\Customers;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Tenant\Resources\Customers\Pages\EditCustomer;
use App\Filament\Tenant\Resources\Customers\Pages\ListCustomers;
use App\Filament\Tenant\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Tenant\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    use HasPermission;
    protected static ?string $model = Customer::class;


    public static function getNavigationLabel(): string
    {
        return __('navigation.customers');
    }


    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.customers');
    }


    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
