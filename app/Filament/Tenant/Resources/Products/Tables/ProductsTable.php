<?php

namespace App\Filament\Tenant\Resources\Products\Tables;

use App\Models\Category;
use App\Models\ProductBrand;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'brand']))
            ->columns([
                Column::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->view('filament.tables.columns.product-name'),
                TextColumn::make('barcode')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('billing_price')
                    ->label('Price')
                    ->money('UGX')
                    ->sortable(),
                TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->placeholder('-'),
                TextColumn::make('current_stock')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->options(fn () => ProductBrand::query()->orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}