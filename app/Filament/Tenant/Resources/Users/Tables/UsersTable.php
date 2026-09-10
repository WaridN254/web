<?php

namespace App\Filament\Tenant\Resources\Users\Tables;

use App\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('User Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('role_id')
                    ->label('Role')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $roleName = 'Unassigned';
                        $validId = $record->role_id ?? $state;
                        if (!empty($validId) && is_string($validId) && strlen($validId) > 10) {
                            static $rolesCache = null;
                            if ($rolesCache === null) {
                                $rolesCache = Role::query()->pluck('name', 'id')->all();
                            }
                            $roleName = $rolesCache[$validId] ?? 'Unassigned';
                        }
                        return $roleName;
                    }),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
