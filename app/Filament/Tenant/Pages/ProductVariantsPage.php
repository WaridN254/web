<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;

class ProductVariantsPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 3;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.product_variants');
    }


    protected static ?string $title = 'Product Variants';

    protected string $view = 'filament.tenant.pages.product-variants-page';

    public bool $showVariantsModal = false;

    public bool $showEditModal = false;

    public ?string $modalProductName = null;

    public ?string $modalProductId = null;

    public array $modalVariants = [];

    public ?string $editingVariantId = null;

    public ?string $editSku = null;

    public ?string $editBarcode = null;

    public ?string $editSellingPriceMode = 'inherited';

    public ?string $editCustomSellingPrice = null;

    public ?string $editCostPriceMode = 'inherited';

    public ?string $editCustomCostPrice = null;

    public ?string $editCurrentStock = null;

    public ?string $editReorderLevel = null;

    public bool $editTrackSerialNumbers = false;

    public bool $editIsActive = true;

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        return $table
            ->query(
                Product::query()
                    ->where('tenant_id', $tenantId)
                    ->where('has_variants', true)
                    ->withCount(['variants as total_variants' => fn ($q) => $q->where('is_active', true)])
                    ->withSum('variants as total_stock', 'current_stock')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('total_variants')
                    ->label('Variants')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => (float) $state <= 0 ? 'danger' : 'success'),
                TextColumn::make('billing_price')
                    ->label('Base Price')
                    ->money(fn () => auth()->user()?->tenant?->business?->currency ?? 'UGX')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->actions([
                Action::make('viewVariants')
                    ->label('View Variants')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(function (Product $record) {
                        $tenantId = auth()->user()?->tenant_id;

                        $this->modalProductName = $record->name;
                        $this->modalProductId = $record->id;
                        $this->modalVariants = ProductVariant::query()
                            ->where('tenant_id', $tenantId)
                            ->where('product_id', $record->id)
                            ->where('is_active', true)
                            ->with('values.attribute', 'values.attributeValue')
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(fn ($v) => [
                                'id' => $v->id,
                                'name' => $v->name,
                                'sku' => $v->sku ?? '—',
                                'barcode' => $v->barcode ?? '—',
                                'selling_price' => (float) $v->effective_selling_price,
                                'selling_price_mode' => $v->selling_price_mode,
                                'cost_price' => (float) $v->effective_cost_price,
                                'cost_price_mode' => $v->cost_price_mode,
                                'stock' => (float) $v->available_stock,
                                'reorder_level' => (float) $v->reorder_level,
                                'track_serial_numbers' => $v->track_serial_numbers,
                                'is_active' => $v->is_active,
                            ])
                            ->toArray();

                        $this->showVariantsModal = true;
                    })
                    ->iconButton(),
            ])
            ->searchPlaceholder('Search products...')
            ->defaultSort('name', 'asc');
    }

    public function closeVariantsModal(): void
    {
        $this->showVariantsModal = false;
        $this->modalProductName = null;
        $this->modalProductId = null;
        $this->modalVariants = [];
    }

    public function openEditModal(string $variantId): void
    {
        $variant = ProductVariant::findOrFail($variantId);

        $this->editingVariantId = $variant->id;
        $this->editSku = $variant->sku;
        $this->editBarcode = $variant->barcode;
        $this->editSellingPriceMode = $variant->selling_price_mode;
        $this->editCustomSellingPrice = $variant->custom_selling_price;
        $this->editCostPriceMode = $variant->cost_price_mode;
        $this->editCustomCostPrice = $variant->custom_cost_price;
        $this->editCurrentStock = (string) $variant->current_stock;
        $this->editReorderLevel = (string) $variant->reorder_level;
        $this->editTrackSerialNumbers = $variant->track_serial_numbers;
        $this->editIsActive = $variant->is_active;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingVariantId = null;
    }

    public function saveVariant(): void
    {
        $variant = ProductVariant::findOrFail($this->editingVariantId);

        $variant->update([
            'sku' => $this->editSku,
            'barcode' => $this->editBarcode,
            'selling_price_mode' => $this->editSellingPriceMode,
            'custom_selling_price' => $this->editSellingPriceMode === 'custom' ? $this->editCustomSellingPrice : null,
            'cost_price_mode' => $this->editCostPriceMode,
            'custom_cost_price' => $this->editCostPriceMode === 'custom' ? $this->editCustomCostPrice : null,
            'current_stock' => $this->editCurrentStock,
            'reorder_level' => $this->editReorderLevel,
            'track_serial_numbers' => $this->editTrackSerialNumbers,
            'is_active' => $this->editIsActive,
        ]);

        $this->closeEditModal();
        $this->closeVariantsModal();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Variant updated successfully',
        ]);
    }

    public function deactivateVariant(string $variantId): void
    {
        ProductVariant::where('id', $variantId)->update(['is_active' => false]);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Variant deactivated',
        ]);

        $this->closeVariantsModal();
    }
}
