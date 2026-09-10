<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Schemas;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BranchService;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        $branchService = app(BranchService::class);
        $activeBranchId = $branchService->getActiveBranchId();

        return $schema
            ->components([
                Section::make('Transfer Details')
                    ->columns(2)
                    ->schema([
                        Select::make('from_branch_id')
                            ->relationship('fromBranch', 'name')
                            ->options(fn () => Branch::active()->where('tenant_id', auth()->user()->tenant_id)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('From Branch')
                            ->default($activeBranchId),
                        Select::make('to_branch_id')
                            ->relationship('toBranch', 'name')
                            ->options(fn () => Branch::active()->where('tenant_id', auth()->user()->tenant_id)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('To Branch'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'in_transit' => 'In Transit',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->required(),
                        Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Transfer notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Transfer Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Product')
                                    ->reactive()
                                    ->afterStateUpdated(function ($set, $state) {
                                        $set('variant_id', null);
                                    }),
                                Select::make('variant_id')
                                    ->options(function ($get) {
                                        $productId = $get('product_id');
                                        if (! $productId) {
                                            return [];
                                        }
                                        return ProductVariant::where('product_id', $productId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->label('Variant'),
                                \Filament\Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->default(1),
                                \Filament\Forms\Components\TextInput::make('unit_cost')
                                    ->numeric()
                                    ->prefix('Unit Cost'),
                                Hidden::make('branch_id')
                                    ->default(fn () => app(BranchService::class)->getActiveBranchId()),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addable()
                            ->deletable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
