<?php

namespace App\Filament\Tenant\Resources\StockMovements\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockMovements\StockMovementResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewStockMovement extends ViewRecord
{
    use HasPermission;
    protected static string $resource = StockMovementResource::class;

    protected static ?string $title = 'Stock Movement Details';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Movement Details')
                    ->schema([
                        TextEntry::make('product.name')->label('Product'),
                        TextEntry::make('supplier.company_name')->label('Supplier')->default('-'),
                        TextEntry::make('movement_type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'in' => 'success',
                                'out' => 'warning',
                                'adjustment' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('quantity')->numeric(),
                        TextEntry::make('reason')->default('-'),
                        TextEntry::make('movement_date')->label('Date')->dateTime(),
                        TextEntry::make('user.full_name')->label('Recorded By')->default('-'),
                    ])->columns(2),
            ]);
    }
}
