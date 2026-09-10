<?php

namespace App\Filament\Tenant\Resources\Products\Schemas;

use App\Models\Category;
use App\Models\ProductBrand;
use App\Models\Supplier;
use App\Models\TaxCategory;
use App\Models\UnitMeasure;
use App\Models\VariantAttribute;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('product_details')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->schema([
                        Tab::make('General')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('category_id')
                                    ->label('Category')
                                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('brand_id')
                                    ->label('Brand')
                                    ->options(fn () => ProductBrand::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->options(fn () => Supplier::query()->orderBy('company_name')->pluck('company_name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('barcode'),
                                FileUpload::make('image_uploads')
                                    ->label('Product Images')
                                    ->image()
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('products')
                                    ->acceptedFileTypes(['image/*'])
                                    ->maxSize(5120)
                                    ->automaticallyResizeImagesMode('contain')
                                    ->automaticallyResizeImagesToWidth(1600)
                                    ->automaticallyResizeImagesToHeight(1600)
                                    ->automaticallyUpscaleImagesWhenResizing(false)
                                    ->imagePreviewHeight(150)
                                    ->itemPanelAspectRatio('1')
                                    ->helperText('Select multiple images from your computer. Large photos are resized automatically on upload.')
                                    ->columnSpanFull(),
                                Textarea::make('image_urls')
                                    ->label('Image URLs (one per line)')
                                    ->helperText('Or paste direct image links — one per line.')
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                                Select::make('status')
                                    ->default('active')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'draft' => 'Draft',
                                    ]),
                                Toggle::make('price_change_allowed')
                                    ->default(false),
                            ]),

                        Tab::make('Pricing')
                            ->columns(2)
                            ->schema([
                                TextInput::make('billing_price')
                                    ->label('Selling Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('UGX')
                                    ->placeholder('0.00'),
                                TextInput::make('cost_price')
                                    ->label('Cost Price')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('UGX')
                                    ->placeholder('0.00'),
                                TextInput::make('tax_rate')
                                    ->label('Tax Rate (%)')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%'),
                                Select::make('tax_category_id')
                                    ->label('Tax Category')
                                    ->options(fn () => TaxCategory::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Tab::make('Stock')
                            ->columns(2)
                            ->visible(fn ($get) => !(bool) $get('has_variants'))
                            ->schema([
                                Toggle::make('track_stock')
                                    ->default(true),
                                Toggle::make('track_serial_numbers')
                                    ->default(false)
                                    ->live(),
                                TextInput::make('current_stock')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('reorder_level')
                                    ->required()
                                    ->numeric()
                                    ->default(10),
                                TextInput::make('unit_of_measure')
                                    ->default('pcs'),
                                DateTimePicker::make('expiration_date'),
                            ]),

                        Tab::make('Variants')
                            ->schema([
                                Toggle::make('has_variants')
                                    ->label('Enable Variants')
                                    ->default(false)
                                    ->live()
                                    ->helperText('Enable this if the product comes in different options (e.g. Size, Color)')
                                    ->columnSpanFull(),
                                Select::make('variant_attribute_ids')
                                    ->label('Variant Attributes')
                                    ->multiple()
                                    ->options(fn () => VariantAttribute::query()
                                        ->where('tenant_id', auth()->user()?->tenant_id)
                                        ->where('is_active', true)
                                        ->with('values')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn ($a) => [$a->id => $a->name . ' (' . $a->values->count() . ' values)'])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn ($livewire) => $livewire->generateVariantRows())
                                    ->visible(fn ($get) => (bool) $get('has_variants'))
                                    ->helperText('Select attributes — variants auto-generate below')
                                    ->columnSpanFull(),
                                View::make('filament.tenant.pages.partials.variant-rows-table')
                                    ->visible(fn ($get) => (bool) $get('has_variants')),
                            ]),

                        Tab::make('Serials')
                            ->visible(fn ($get) => (bool) $get('track_serial_numbers') && !(bool) $get('has_variants'))
                            ->schema([
                                Repeater::make('serials')
                                    ->label('Serial Numbers')
                                    ->helperText('Add each serial number the product has, or scan them in one by one.')
                                    ->relationship('serials')
                                    ->defaultItems(0)
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('serial_number')
                                            ->label('Serial Number')
                                            ->required(),
                                        TextInput::make('box_serial_number')
                                            ->label('Box Serial Number'),
                                        TextInput::make('lot_number')
                                            ->label('Lot Number'),
                                        TextInput::make('material_code')
                                            ->label('Material Code'),
                                        TextInput::make('model_number')
                                            ->label('Model Number'),
                                        Select::make('status')
                                            ->label('Status')
                                            ->default('available')
                                            ->options([
                                                'available' => 'Available',
                                                'reserved' => 'Reserved',
                                                'sold' => 'Sold',
                                                'returned' => 'Returned',
                                            ]),
                                        Textarea::make('notes')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Warranty')
                            ->columns(2)
                            ->schema([
                                Toggle::make('warranty_enabled')
                                    ->default(false),
                                TextInput::make('warranty_period_days')
                                    ->label('Warranty Period (days)')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('guarantee_period_days')
                                    ->label('Guarantee Period (days)')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('warranty_provider')
                                    ->label('Warranty Provider')
                                    ->default('customer')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Bundle')
                            ->columns(2)
                            ->schema([
                                Toggle::make('is_bundle')
                                    ->default(false),
                                TextInput::make('items_per_bundle')
                                    ->label('Items per Bundle')
                                    ->numeric()
                                    ->default(1),
                            ]),

                        Tab::make('Multi Selling')
                            ->schema([
                                Select::make('base_unit_id')
                                    ->label('Base Unit')
                                    ->options(fn () => UnitMeasure::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Select::make('purchase_unit_id')
                                    ->label('Purchase Unit')
                                    ->options(fn () => UnitMeasure::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Select::make('default_sale_unit_id')
                                    ->label('Default Sale Unit')
                                    ->options(fn () => UnitMeasure::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Repeater::make('saleUnits')
                                    ->label('Sale Units')
                                    ->helperText('Add each unit this product is sold in.')
                                    ->relationship('saleUnits')
                                    ->defaultItems(0)
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('unit_name')
                                            ->label('Unit Name')
                                            ->required(),
                                        TextInput::make('unit_label')
                                            ->label('Unit Label')
                                            ->required(),
                                        TextInput::make('conversion_to_base_unit')
                                            ->label('Conversion to Base Unit')
                                            ->numeric()
                                            ->default(1),
                                        TextInput::make('price')
                                            ->label('Price')
                                            ->numeric()
                                            ->prefix('UGX'),
                                        Toggle::make('allow_decimal')
                                            ->label('Allow decimals')
                                            ->default(false),
                                        Toggle::make('is_default')
                                            ->label('Default sale unit')
                                            ->default(false),
                                    ]),
                            ]),

                        Tab::make('System')
                            ->columns(2)
                            ->schema([
                                Toggle::make('is_deleted')
                                    ->default(false),
                                Select::make('sync_status')
                                    ->default('pending')
                                    ->options([
                                        'pending' => 'Pending',
                                        'synced' => 'Synced',
                                        'failed' => 'Failed',
                                    ]),
                                DateTimePicker::make('last_synced_at'),
                            ]),
                    ]),
            ]);
    }
}