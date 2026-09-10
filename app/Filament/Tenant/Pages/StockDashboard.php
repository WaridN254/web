<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Filament\Tenant\Widgets\StockOverviewWidget;
use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\Alignment;

class StockDashboard extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 40;

    public ?string $selectedProductId = null;

    protected function getListeners(): array
    {
        return [
            'scanner-product-found' => 'onScannerProductFound',
        ];
    }

    public function onScannerProductFound(array $data): void
    {
        $productId = $data['product_id'];
        $product = \App\Models\Product::find($productId);
        if (!$product) return;

        $this->selectedProductId = $productId;
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Product found', 'description' => $product->name . ' — Stock: ' . $product->stock_quantity]);
    }


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.stock');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.stock_dashboard');
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.stock-dashboard';

    protected static ?string $title = 'Monitor and control your inventory levels across all items.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('performAudit')
                ->label('Perform Audit')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(url('/tenant/stock-audits')), // We will create this resource later

            Action::make('stockReturn')
                ->label('Stock Return')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->outlined()
                ->form([
                    \Filament\Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->options(\App\Models\Product::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('available_stock', \App\Models\Product::find($state)?->stock_quantity ?? 0)),
                    \Filament\Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(\App\Models\Supplier::query()->pluck('company_name', 'id'))
                        ->searchable(),
                    \Filament\Forms\Components\TextInput::make('reference')
                        ->label('Reference')
                        ->placeholder('Supplier RMA / credit note / invoice ref'),
                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->required()
                        ->rules([
                            fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (floatval($value) > floatval($get('available_stock'))) {
                                    $fail('Cannot return more than available stock.');
                                }
                            },
                        ]),
                    \Filament\Forms\Components\Placeholder::make('available_stock_display')
                        ->label('')
                        ->content(fn ($get) => 'Available stock: ' . ($get('available_stock') ?? '0.0')),
                    \Filament\Forms\Components\Hidden::make('available_stock'),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Reason / Notes')
                        ->placeholder('e.g. Wrong delivery, defective batch, supplier recall'),
                ])
                ->action(function (array $data) {
                    \App\Models\StockMovement::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'branch_id' => app(\App\Services\BranchService::class)->getActiveBranchId(),
                        'product_id' => $data['product_id'],
                        'movement_type' => 'out',
                        'quantity' => $data['quantity'],
                        'reason' => $data['notes'] ?? null,
                        'reference_id' => $data['reference'] ?? null,
                        'supplier_id' => $data['supplier_id'] ?? null,
                        'user_id' => auth()->id(),
                        'movement_date' => now(),
                    ]);
                    
                    $this->dispatch('toast', [
                        'type' => 'success',
                        'title' => 'Stock returned successfully',
                    ]);
                }),

            Action::make('addStock')
                ->label('Add Stock')
                ->icon('heroicon-o-plus')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->options(\App\Models\Product::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->required()
                        ->minValue(0.01),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Reason / Notes')
                        ->placeholder('e.g. New delivery'),
                ])
                ->action(function (array $data) {
                    \App\Models\StockMovement::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'branch_id' => app(\App\Services\BranchService::class)->getActiveBranchId(),
                        'product_id' => $data['product_id'],
                        'movement_type' => 'in',
                        'quantity' => $data['quantity'],
                        'reason' => $data['notes'] ?? null,
                        'user_id' => auth()->id(),
                        'movement_date' => now(),
                    ]);

                    $this->dispatch('toast', [
                        'type' => 'success',
                        'title' => 'Stock added successfully',
                    ]);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockOverviewWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()
                ->with('variants')
                ->withCount(['serials as available_serial_count' => fn ($q) => $q->where('status', 'available')]))
            ->columns([
                TextColumn::make('barcode')
                    ->label('BARCODE')
                    ->searchable()
                    ->default('N/A'),
                TextColumn::make('name')
                    ->label('NAME')
                    ->searchable(),
                TextColumn::make('stock_quantity')
                    ->label('QTY')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('UNIT')
                    ->default('pcs'),
                TextColumn::make('cost_price')
                    ->label('UNIT COST')
                    ->money('UGX')
                    ->sortable(),
                TextColumn::make('billing_price')
                    ->label('UNIT SELL')
                    ->money('UGX')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('TOTAL COST')
                    ->state(function (Product $record): float {
                        return floatval($record->stock_quantity) * floatval($record->cost_price);
                    })
                    ->money('UGX'),
                TextColumn::make('total_value')
                    ->label('TOTAL VALUE')
                    ->state(function (Product $record): float {
                        return floatval($record->stock_quantity) * floatval($record->billing_price);
                    })
                    ->money('UGX'),
            ])
            ->filters([
                Filter::make('negative_quantity')
                    ->label('Negative quantity')
                    ->query(fn (Builder $query): Builder => $query->where('current_stock', '<', 0)->where('track_serial_numbers', false)),
                Filter::make('non_zero_quantity')
                    ->label('Non zero quantity')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->where('current_stock', '!=', 0)->where('track_serial_numbers', false);
                    })->orWhere(function ($q) {
                        $q->where('track_serial_numbers', true)->whereHas('serials', fn ($sq) => $sq->where('status', 'available'));
                    })),
                Filter::make('zero_quantity')
                    ->label('Zero quantity')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->where('current_stock', 0)->where('track_serial_numbers', false);
                    })->orWhere(function ($q) {
                        $q->where('track_serial_numbers', true)->whereDoesntHave('serials', fn ($sq) => $sq->where('status', 'available'));
                    })),
                Filter::make('expiring_soon')
                    ->label('Expiring Soon')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('expiration_date')->where('expiration_date', '<=', now()->addDays(30))),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->searchPlaceholder('Search stock by product name or barcode...');
    }
}
