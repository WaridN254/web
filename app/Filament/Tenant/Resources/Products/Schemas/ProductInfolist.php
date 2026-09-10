<?php

namespace App\Filament\Tenant\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Product'),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->placeholder('-'),
                        TextEntry::make('brand.name')
                            ->label('Brand')
                            ->placeholder('-'),
                        TextEntry::make('unit_of_measure')
                            ->label('Unit')
                            ->placeholder('-'),
                        TextEntry::make('barcode')
                            ->label('SKU')
                            ->placeholder('-'),
                        TextEntry::make('supplier.company_name')
                            ->label('Supplier')
                            ->placeholder('-'),
                        TextEntry::make('reorder_level')
                            ->label('Minimum Qty'),
                        TextEntry::make('current_stock')
                            ->label('Quantity'),
                        TextEntry::make('tax_rate')
                            ->label('Tax')
                            ->suffix('%'),
                        TextEntry::make('billing_price')
                            ->label('Price')
                            ->money('UGX'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'gray',
                                'draft' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('expiration_date')
                            ->label('Expiry Date')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Product Images')
                    ->schema([
                        ImageEntry::make('images')
                            ->label('Gallery')
                            ->height(160)
                            ->square()
                            ->extraImgAttributes(['loading' => 'lazy'])
                            ->state(function (Product $record): array {
                                $urls = $record->images
                                    ->map(fn ($image) => $image->image_path
                                        ? url('product-images/' . $image->image_path)
                                        : $image->image_url)
                                    ->all();

                                if (! $urls && $record->image_url) {
                                    return [$record->image_url];
                                }

                                return $urls;
                            }),
                    ]),
            ]);
    }
}