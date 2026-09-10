<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Product;
use App\Models\ProductSerial;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ProductSerialsPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?int $navigationSort = 2;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.serials');
    }


    protected static ?string $title = 'Product Serials';

    protected string $view = 'filament.tenant.pages.product-serials-page';

    public bool $showSerialsModal = false;

    public ?string $modalProductName = null;

    public array $modalSerials = [];

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        return $table
            ->query(
                Product::query()
                    ->where('tenant_id', $tenantId)
                    ->where('track_serial_numbers', true)
                    ->withCount(['serials as total_serials'])
                    ->withCount(['serials as available_serials' => fn ($q) => $q->where('status', 'available')])
                    ->withCount(['serials as sold_serials' => fn ($q) => $q->where('status', 'sold')])
                    ->withCount(['serials as returned_serials' => fn ($q) => $q->where('status', 'returned')])
                    ->withCount(['serials as damaged_serials' => fn ($q) => $q->where('status', 'damaged')])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('total_serials')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('available_serials')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->color('success'),
                TextColumn::make('sold_serials')
                    ->label('Sold')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                TextColumn::make('returned_serials')
                    ->label('Returned')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('damaged_serials')
                    ->label('Damaged')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                TextColumn::make('billing_price')
                    ->label('Price')
                    ->money(fn () => auth()->user()?->tenant?->business?->currency ?? 'UGX')
                    ->sortable(),
            ])
            ->actions([
                Action::make('viewSerials')
                    ->label('View Serials')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(function (Product $record) {
                        $tenantId = auth()->user()?->tenant_id;

                        $this->modalProductName = $record->name;
                        $this->modalSerials = ProductSerial::query()
                            ->where('tenant_id', $tenantId)
                            ->where('product_id', $record->id)
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(fn ($s) => [
                                'serial_number' => $s->serial_number,
                                'box_serial_number' => $s->box_serial_number ?? '—',
                                'status' => $s->status,
                                'cost_price' => (float) ($s->cost_price ?: $record->cost_price),
                                'selling_price' => (float) ($s->selling_price ?: $record->billing_price),
                                'received_at' => $s->received_at?->format('d M Y') ?? '—',
                                'sold_at' => $s->sold_at?->format('d M Y') ?? '—',
                                'notes' => $s->notes ?? '',
                            ])
                            ->toArray();

                        $this->showSerialsModal = true;
                    })
                    ->iconButton(),
            ])
            ->searchPlaceholder('Search products...')
            ->defaultSort('name', 'asc');
    }

    public function closeSerialsModal(): void
    {
        $this->showSerialsModal = false;
        $this->modalProductName = null;
        $this->modalSerials = [];
    }
}
