<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('purchase_number')
                    ->default(fn () => 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)))
                    ->readOnly()
                    ->required()
                    ->maxLength(255),
                Select::make('supplier_id')
                    ->relationship('supplier', 'company_name')
                    ->searchable(),
                DateTimePicker::make('purchase_date')
                    ->default(now())
                    ->required(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('draft'),
                Select::make('receiving_status')
                    ->options([
                        'not_received' => 'Not Received',
                        'partially_received' => 'Partially Received',
                        'fully_received' => 'Fully Received',
                    ])
                    ->required()
                    ->default('not_received'),
                Select::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                    ])
                    ->required()
                    ->default('unpaid'),
                
                Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('unit_cost', $product->cost_price ?? 0);
                                }
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live(debounce: 500),
                        TextInput::make('unit_cost')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->live(debounce: 500),
                        Hidden::make('branch_id')
                            ->default(fn () => app(\App\Services\BranchService::class)->getActiveBranchId()),
                    ])
                    ->live()
                    ->columns(3)
                    ->columnSpanFull(),

                TextInput::make('discount')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->live(debounce: 500),
                TextInput::make('tax')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->live(debounce: 500),
                TextInput::make('shipping')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->live(debounce: 500),

                Placeholder::make('subtotal_display')
                    ->label('Subtotal')
                    ->content(function ($get) {
                        $items = $get('items') ?? [];
                        $subtotal = 0;
                        foreach ($items as $item) {
                            $subtotal += floatval($item['quantity'] ?? 0) * floatval($item['unit_cost'] ?? 0);
                        }
                        return number_format($subtotal, 2);
                    }),
                Placeholder::make('grand_total_display')
                    ->label('Grand Total')
                    ->content(function ($get) {
                        $items = $get('items') ?? [];
                        $subtotal = 0;
                        foreach ($items as $item) {
                            $subtotal += floatval($item['quantity'] ?? 0) * floatval($item['unit_cost'] ?? 0);
                        }
                        $discount = floatval($get('discount') ?? 0);
                        $tax = floatval($get('tax') ?? 0);
                        $shipping = floatval($get('shipping') ?? 0);
                        $grandTotal = $subtotal - $discount + $tax + $shipping;
                        return number_format($grandTotal, 2);
                    }),
                
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
