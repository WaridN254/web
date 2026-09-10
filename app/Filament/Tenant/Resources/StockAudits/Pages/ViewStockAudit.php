<?php

namespace App\Filament\Tenant\Resources\StockAudits\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockAudits\StockAuditResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewStockAudit extends ViewRecord
{
    use HasPermission;
    protected static string $resource = StockAuditResource::class;

    protected static ?string $title = 'Stock Audit Details';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Summary')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'warning',
                                'finalized' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('start_date')->label('Started At')->dateTime(),
                        TextEntry::make('end_date')->label('Finalized At')->dateTime()->default('-'),
                        TextEntry::make('auditor.full_name')->label('Auditor')->default('-'),
                        TextEntry::make('notes')->label('Notes')->default('-')->columnSpanFull(),
                    ])->columns(2),

                Section::make('Inventory Counts')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('product_name')->label('Product'),
                                TextEntry::make('system_qty')->label('Expected Stock'),
                                TextEntry::make('physical_qty')->label('Physical Count'),
                                TextEntry::make('discrepancy')->label('Discrepancy'),
                            ])
                            ->columns(4)
                            ->placeholder('No inventory items recorded yet.'),
                    ]),
            ]);
    }
}
