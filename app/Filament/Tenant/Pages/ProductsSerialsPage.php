<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Product;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductsSerialsPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?int $navigationSort = 1;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.serials');
    }


    protected static ?string $title = 'Products with Serial Numbers';

    protected string $view = 'filament.tenant.pages.products-serials-page';

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        $serialCounts = DB::table('product_serials')
            ->where('tenant_id', $tenantId)
            ->selectRaw('product_id, status, count(*) as total')
            ->groupBy('product_id', 'status')
            ->get()
            ->groupBy('product_id')
            ->mapWithKeys(fn ($groups, $productId) => [
                $productId => $groups->pluck('total', 'status')->toArray(),
            ]);

        return $table
            ->query(
                Product::query()
                    ->where('tenant_id', $tenantId)
                    ->where('track_serial_numbers', true)
                    ->withCount(['serials as total_serials' => fn ($q) => $q->where('tenant_id', $tenantId)])
            )
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('barcode')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_serials')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('available_count')
                    ->label('Available')
                    ->state(fn ($record) => $serialCounts[$record->id]['available'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('success'),
                TextColumn::make('sold_count')
                    ->label('Sold')
                    ->state(fn ($record) => $serialCounts[$record->id]['sold'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                TextColumn::make('returned_count')
                    ->label('Returned')
                    ->state(fn ($record) => $serialCounts[$record->id]['returned'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('reserved_count')
                    ->label('Reserved')
                    ->state(fn ($record) => $serialCounts[$record->id]['reserved'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('info'),
                TextColumn::make('transferred_count')
                    ->label('Transferred')
                    ->state(fn ($record) => $serialCounts[$record->id]['transferred'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('damaged_count')
                    ->label('Damaged')
                    ->state(fn ($record) => $serialCounts[$record->id]['damaged'] ?? 0)
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
            ])
            ->filters([])
            ->searchPlaceholder('Search products...')
            ->defaultSort('name', 'asc');
    }
}
