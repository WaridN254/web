<?php

namespace App\Filament\Tenant\Resources\Users\Schemas;

use App\Models\Branch;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')->schema([
                    TextInput::make('full_name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(255),

                    TextInput::make('password_hash')
                        ->password()
                        ->label('Password')
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => \Illuminate\Support\Facades\Hash::make($state)),

                    Select::make('role_id')
                        ->label('Role')
                        ->options(fn () => Role::query()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('Select a role'),

                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true),
                ])->columns(2),

                Section::make('Branch Assignment')->schema([
                    Select::make('default_branch_id')
                        ->label('Default Branch')
                        ->options(fn () => Branch::where('tenant_id', auth()->user()->tenant_id)
                            ->where('is_active', true)
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('Select default branch'),

                    Toggle::make('can_view_all_branches')
                        ->label('Can View All Branches')
                        ->helperText('If enabled, user can see and switch between all branches. If disabled, only assigned branches.')
                        ->default(false),

                    Toggle::make('can_manage_branches')
                        ->label('Can Manage Branches')
                        ->helperText('Create, edit and deactivate branches.')
                        ->default(false),

                    Toggle::make('can_manage_stock_transfers')
                        ->label('Can Manage Stock Transfers')
                        ->helperText('Create and manage stock transfers between branches.')
                        ->default(false),
                ])->columns(3),
            ]);
    }
}
