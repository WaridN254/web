<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchasePayment;
use App\Models\StockMovement;
use App\Models\ProductSerial;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BranchService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class ViewPurchaseOrder extends ViewRecord
{
    use HasPermission;
    protected static string $resource = PurchaseOrderResource::class;

    protected static ?string $title = 'Purchase Order Details';

    protected string $view = 'filament.tenant.resources.purchase-orders.pages.view-purchase-order';

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

        $item = $this->record->items()->where('product_id', $productId)->first();
        if (!$item) {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'Product not in this order', 'description' => $product->name]);
            return;
        }

        $remaining = floatval($item->quantity) - floatval($item->received_quantity);
        if ($remaining <= 0) {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'Already fully received', 'description' => $product->name]);
            return;
        }

        $item->increment('received_quantity');
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Received +1', 'description' => $product->name]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // TOP SECTION — Summary
                Section::make('Summary')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('purchase_number')->label('PO Number'),
                        TextEntry::make('supplier.company_name')->label('Supplier'),
                        TextEntry::make('purchase_date')->label('Date')->dateTime(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'submitted' => 'info',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('receiving_status')->label('Receiving')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'not_received' => 'danger',
                                'partially_received' => 'warning',
                                'fully_received' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('subtotal')->money('UGX'),
                        TextEntry::make('discount')->money('UGX'),
                        TextEntry::make('tax')->money('UGX'),
                        TextEntry::make('shipping')->money('UGX'),
                        TextEntry::make('grand_total')->money('UGX')->weight('bold'),
                        TextEntry::make('paid_amount')->money('UGX')
                            ->color('success'),
                        TextEntry::make('due_amount')->money('UGX')
                            ->color(fn ($state) => floatval($state) > 0 ? 'danger' : 'success'),
                    ]),

                // MIDDLE SECTION — Order Items
                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('product_name')->label('Product'),
                                TextEntry::make('sku')->label('SKU')->default('-'),
                                TextEntry::make('quantity')->label('Ordered'),
                                TextEntry::make('received_quantity')->label('Received'),
                                TextEntry::make('remaining')
                                    ->label('Remaining')
                                    ->state(fn ($record) => floatval($record->quantity) - floatval($record->received_quantity)),
                                TextEntry::make('unit_cost')->label('Cost')->money('UGX'),
                                TextEntry::make('total')->label('Total')->money('UGX')->weight('bold'),
                            ])
                            ->columns(7),
                    ]),

                // BOTTOM SECTION — Payments History
                Section::make('Payments History')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('payment_date')->label('Date')->dateTime(),
                                TextEntry::make('amount')->label('Amount')->money('UGX')->weight('bold'),
                                TextEntry::make('payment_method')->label('Method'),
                                TextEntry::make('payment_source')->label('Source'),
                                TextEntry::make('reference_number')->label('Reference')->default('-'),
                            ])
                            ->columns(5)
                            ->placeholder('No payments recorded yet.'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // RECEIVE STOCK ACTION
            Action::make('receiveStock')
                ->label('Receive Stock')
                ->icon('heroicon-o-cube')
                ->color('success')
                ->visible(fn () => $this->record->receiving_status !== 'fully_received')
                ->form(function () {
                    $fields = [];
                    foreach ($this->record->items as $item) {
                        $remaining = floatval($item->quantity) - floatval($item->received_quantity);
                        if ($remaining <= 0) continue;

                        $product = Product::with('variants')->find($item->product_id);
                        $isSerialized = $product && ($product->track_serial_numbers || $product->has_variants);
                        $hasVariants = $product && $product->has_variants;

                        if ($hasVariants) {
                            // Show variant selector for each variant of this product
                            foreach ($product->variants->where('is_active', true) as $variant) {
                                $fields[] = Select::make("variant_items.{$variant->id}")
                                    ->label("{$product->name} — {$variant->name}")
                                    ->options([
                                        'quantity' => 'Receive by Quantity',
                                        'serials' => 'Receive by Serial Numbers',
                                    ])
                                    ->default($variant->track_serial_numbers ? 'serials' : 'quantity')
                                    ->reactive()
                                    ->columnSpanFull();
                            }
                        } elseif ($isSerialized) {
                            $fields[] = Textarea::make("serials.{$item->id}")
                                ->label("{$item->product_name} — Serial Numbers")
                                ->helperText("Ordered {$item->quantity} | Received {$item->received_quantity} | Remaining {$remaining} | Enter one serial number per line")
                                ->rows(min($remaining, 5))
                                ->placeholder("Enter serial numbers, one per line...")
                                ->required();
                        } else {
                            $fields[] = TextInput::make("items.{$item->id}")
                                ->label("{$item->product_name}")
                                ->helperText("Ordered {$item->quantity} | Received {$item->received_quantity} | Remaining {$remaining}")
                                ->numeric()
                                ->default($remaining)
                                ->maxValue($remaining)
                                ->minValue(0)
                                ->required();
                        }
                    }

                    // Variant serial/quantity inputs
                    foreach ($this->record->items as $item) {
                        $product = Product::with('variants')->find($item->product_id);
                        if (!$product || !$product->has_variants) continue;

                        foreach ($product->variants->where('is_active', true) as $variant) {
                            if ($variant->track_serial_numbers) {
                                $fields[] = Textarea::make("variant_serials.{$variant->id}")
                                    ->label("{$variant->name} — Serial Numbers")
                                    ->helperText("Enter one serial number per line for {$variant->name}")
                                    ->rows(3)
                                    ->placeholder("Enter serial numbers, one per line...")
                                    ->visible(fn ($get) => ($get("variant_items.{$variant->id}") ?? 'serials') === 'serials')
                                    ->columnSpanFull();
                            } else {
                                $fields[] = TextInput::make("variant_quantities.{$variant->id}")
                                    ->label("{$variant->name} — Quantity")
                                    ->helperText("Current stock: " . number_format($variant->available_stock))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->visible(fn ($get) => ($get("variant_items.{$variant->id}") ?? 'quantity') === 'quantity')
                                    ->columnSpanFull();
                            }
                        }
                    }

                    $fields[] = Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Optional receiving notes');
                    return $fields;
                })
                ->modalHeading('Receive Stock')
                ->modalDescription('Enter quantities or serial numbers for items being received.')
                ->modalSubmitActionLabel('Receive')
                ->action(function (array $data) {
                    $items = $data['items'] ?? [];
                    $serials = $data['serials'] ?? [];
                    $variantItems = $data['variant_items'] ?? [];
                    $variantSerials = $data['variant_serials'] ?? [];
                    $variantQuantities = $data['variant_quantities'] ?? [];
                    $notes = $data['notes'] ?? null;
                    $tenantId = auth()->user()->tenant_id;
                    $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

                    // Handle non-serialized items (quantity-based)
                    foreach ($items as $itemId => $qty) {
                        $qty = floatval($qty);
                        if ($qty <= 0) continue;

                        $item = $this->record->items()->find($itemId);
                        if (!$item) continue;

                        $item->received_quantity = floatval($item->received_quantity) + $qty;
                        $item->saveQuietly();

                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'supplier_id' => $this->record->supplier_id,
                            'user_id' => auth()->id(),
                            'branch_id' => $branchId,
                            'movement_type' => 'in',
                            'quantity' => $qty,
                            'reference_id' => $this->record->id,
                            'reason' => 'PO Receive: ' . $this->record->purchase_number . ($notes ? " — {$notes}" : ''),
                            'movement_date' => now(),
                            'tenant_id' => $tenantId,
                        ]);
                    }

                    // Handle serialized items (serial number-based)
                    foreach ($serials as $itemId => $serialText) {
                        $serialText = trim($serialText ?? '');
                        if ($serialText === '') continue;

                        $item = $this->record->items()->find($itemId);
                        if (!$item) continue;

                        $product = Product::find($item->product_id);
                        if (!$product) continue;

                        $serialNumbers = array_filter(array_map('trim', explode("\n", $serialText)));
                        $receivedCount = count($serialNumbers);

                        if ($receivedCount <= 0) continue;

                        $item->received_quantity = floatval($item->received_quantity) + $receivedCount;
                        $item->saveQuietly();

                        foreach ($serialNumbers as $serialNum) {
                            if ($serialNum === '') continue;

                            ProductSerial::create([
                                'tenant_id' => $tenantId,
                                'product_id' => $item->product_id,
                                'serial_number' => $serialNum,
                                'purchase_id' => $this->record->id,
                                'supplier_id' => $this->record->supplier_id,
                                'status' => 'available',
                                'cost_price' => $item->unit_cost,
                                'received_at' => now(),
                                'notes' => 'PO: ' . $this->record->purchase_number,
                            ]);

                            StockMovement::create([
                                'product_id' => $item->product_id,
                                'serial_number' => $serialNum,
                                'supplier_id' => $this->record->supplier_id,
                                'user_id' => auth()->id(),
                                'branch_id' => $branchId,
                                'movement_type' => 'in',
                                'quantity' => 1,
                                'reference_id' => $this->record->id,
                                'reason' => 'PO Receive: ' . $this->record->purchase_number . ($notes ? " — {$notes}" : ''),
                                'movement_date' => now(),
                                'tenant_id' => $tenantId,
                            ]);
                        }
                    }

                    // Handle variant-level receiving
                    foreach ($variantQuantities as $variantId => $qty) {
                        $qty = floatval($qty);
                        if ($qty <= 0) continue;

                        $variant = ProductVariant::find($variantId);
                        if (!$variant) continue;
                        
                        $item = $this->record->items()->where('product_id', $variant->product_id)->first();
                        if ($item) {
                            $item->received_quantity = floatval($item->received_quantity) + $qty;
                            $item->saveQuietly();
                        }

                        StockMovement::create([
                            'product_id' => $variant->product_id,
                            'variant_id' => $variant->id,
                            'supplier_id' => $this->record->supplier_id,
                            'user_id' => auth()->id(),
                            'branch_id' => $branchId,
                            'movement_type' => 'in',
                            'quantity' => $qty,
                            'reference_id' => $this->record->id,
                            'reason' => 'PO Receive (variant): ' . $variant->name . ' — ' . $this->record->purchase_number . ($notes ? " — {$notes}" : ''),
                            'movement_date' => now(),
                            'tenant_id' => $tenantId,
                        ]);
                    }

                    // Handle variant-level serial receiving
                    foreach ($variantSerials as $variantId => $serialText) {
                        $serialText = trim($serialText ?? '');
                        if ($serialText === '') continue;

                        $variant = ProductVariant::find($variantId);
                        if (!$variant) continue;

                        $serialNumbers = array_filter(array_map('trim', explode("\n", $serialText)));
                        $receivedCount = count($serialNumbers);

                        if ($receivedCount <= 0) continue;
                        
                        $item = $this->record->items()->where('product_id', $variant->product_id)->first();
                        if ($item) {
                            $item->received_quantity = floatval($item->received_quantity) + $receivedCount;
                            $item->saveQuietly();
                        }

                        foreach ($serialNumbers as $serialNum) {
                            if ($serialNum === '') continue;

                            ProductSerial::create([
                                'tenant_id' => $tenantId,
                                'product_id' => $variant->product_id,
                                'variant_id' => $variant->id,
                                'serial_number' => $serialNum,
                                'purchase_id' => $this->record->id,
                                'supplier_id' => $this->record->supplier_id,
                                'status' => 'available',
                                'cost_price' => $variant->effective_cost_price,
                                'received_at' => now(),
                                'notes' => 'PO: ' . $this->record->purchase_number . ' — ' . $variant->name,
                            ]);

                            StockMovement::create([
                                'product_id' => $variant->product_id,
                                'variant_id' => $variant->id,
                                'serial_number' => $serialNum,
                                'supplier_id' => $this->record->supplier_id,
                                'user_id' => auth()->id(),
                                'branch_id' => $branchId,
                                'movement_type' => 'in',
                                'quantity' => 1,
                                'reference_id' => $this->record->id,
                                'reason' => 'PO Receive (variant): ' . $variant->name . ' — ' . $this->record->purchase_number . ($notes ? " — {$notes}" : ''),
                                'movement_date' => now(),
                                'tenant_id' => $tenantId,
                            ]);
                        }
                    }

                    $this->record->updateReceivingStatus();

                    Cache::forget("tenant:{$tenantId}:widget_stats");
                    Cache::forget("tenant:{$tenantId}:widget_revenue");
                    Cache::forget("tenant:{$tenantId}:widget_sales_dashboard");

                    $this->dispatch('toast', ['type' => 'success', 'title' => 'Stock received successfully']);

                    $this->redirect(PurchaseOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            // PRINT ACTION
            Action::make('printPurchaseOrder')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    $this->js('window.print()');
                }),

            // ADD PAYMENT ACTION
            Action::make('addPayment')
                ->label('Add Payment')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->visible(fn () => floatval($this->record->due_amount) > 0)
                ->form([
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required()
                        ->default(fn () => floatval($this->record->due_amount))
                        ->maxValue(fn () => floatval($this->record->due_amount)),
                    Select::make('payment_method')
                        ->label('Payment Method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'mobile_money' => 'Mobile Money',
                            'cheque' => 'Cheque',
                        ])
                        ->required()
                        ->default('cash'),
                    Select::make('payment_source')
                        ->label('Payment Source')
                        ->options([
                            'cash_drawer' => 'Cash Drawer',
                            'bank' => 'Bank Account',
                            'mobile' => 'Mobile Wallet',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->default('cash_drawer'),
                    TextInput::make('reference_number')
                        ->label('Reference (Optional)'),
                ])
                ->modalHeading('Add Payment')
                ->modalSubmitActionLabel('Save Payment')
                ->action(function (array $data) {
                    PurchasePayment::create([
                        'purchase_id' => $this->record->id,
                        'supplier_id' => $this->record->supplier_id,
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method'],
                        'payment_source' => $data['payment_source'],
                        'reference_number' => $data['reference_number'] ?? null,
                        'payment_date' => now(),
                        'paid_by' => auth()->id(),
                        'tenant_id' => auth()->user()->tenant_id,
                        'branch_id' => app(\App\Services\BranchService::class)->getActiveBranchId(),
                    ]);

                    $this->record->recalculatePayments();

                    $this->dispatch('toast', ['type' => 'success', 'title' => 'Payment recorded successfully']);

                    $this->redirect(PurchaseOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            // EDIT (only for draft/submitted)
            EditAction::make()
                ->visible(fn () => !in_array($this->record->status, ['approved', 'rejected'])),
        ];
    }
}
