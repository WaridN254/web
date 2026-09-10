<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SalesTerminal extends Page
{
    use HasPermission;
    public string $search = '';

    public string $selectedCategory = 'all';

    public ?string $customerId = null;

    public ?string $paymentMethodId = null;

    public null|float|int|string $orderDiscount = 0;

    public null|float|int|string $amountReceived = 0;

    public array $cart = [];

    public ?string $lastReceiptNumber = null;

    public bool $showOrdersModal = false;

    public bool $showPaymentModal = false;

    public bool $showAddCustomerModal = false;

    public bool $showReceiptModal = false;

    public ?string $lastTransactionId = null;

    public string $ordersTab = 'paid';

    public array $collectAmounts = [];

    public int $renderKey = 0;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerEmail = '';

    public string $newCustomerTin = '';

    public string $newCustomerAddress = '';

    public bool $splitPayment = false;

    public array $splitPayments = [];

    public string $paymentType = 'cash';

    public ?string $layawayDueDate = null;

    // Cash In / Cash Out
    public bool $showCashModal = false;

    public string $cashMovementType = 'cash_in';

    public string $cashAmount = '';

    public string $cashReason = '';

    // X/Z Report
    public bool $showReportModal = false;

    public string $reportType = 'x';

    public string $zActualCash = '';

    public string $zNote = '';

    public float $shortage = 0;

    // Serial selection
    public bool $showSerialModal = false;

    public ?string $serialModalProductId = null;

    public string $serialModalProductName = '';

    public array $serialModalAvailable = [];

    public array $serialModalSelected = [];

    public string $serialSearch = '';

    // Variant selection
    public bool $showVariantModal = false;

    public ?string $variantModalProductId = null;

    public string $variantModalProductName = '';

    public array $variantModalVariants = [];

    public ?string $selectedVariantId = null;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-computer-desktop';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?int $navigationSort = 1;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.sales');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.pos');
    }


    protected string $view = 'filament.tenant.pages.sales-terminal';

    protected static ?string $title = 'Sales Terminal';

    protected \Filament\Support\Enums\Width|string|null $maxContentWidth = 'full';

    public function mount(): void
    {
        $this->paymentMethodId = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->value('id');
    }

    public function dashboardAccess(): array
    {
        $user = auth()->user();
        $canAdmin = (bool) ($user?->hasPermission('can_access_admin_dashboard') ?? false);
        $canSales = (bool) ($user?->hasPermission('can_access_sales_dashboard') ?? false);
        $canDash2 = (bool) ($user?->hasPermission('can_access_dashboard_2') ?? false);

        $allowed = match (true) {
            $canSales => ['label' => 'Sales Dashboard', 'url' => route('filament.tenant.pages.sales-dashboard')],
            $canDash2 => ['label' => 'Admin Dashboard 2', 'url' => route('filament.tenant.pages.admin-dashboard-2')],
            $canAdmin => ['label' => 'Admin Dashboard', 'url' => route('filament.tenant.pages.admin-dashboard')],
            default => null,
        };

        return [
            'canAdmin' => $canAdmin,
            'allowed' => $allowed,
        ];
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function getExtraBodyAttributes(): array
    {
        return ['class' => 'dreams-pos-body'];
    }

    public function categories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function products()
    {
        return Product::query()
            ->with('category')
            ->withCount(['serials as available_serial_count' => fn ($q) => $q->where('status', 'available')])
            ->when($this->selectedCategory !== 'all', fn ($query) => $query->where('category_id', $this->selectedCategory))
            ->when($this->search !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('barcode', 'like', $term)
                        ->orWhereHas('variants', function ($q) use ($term) {
                            $q->where('sku', 'like', $term)
                                ->orWhere('barcode', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        });
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(36)
            ->get();
    }

    public function customers()
    {
        return Customer::query()
            ->orderBy('full_name')
            ->limit(100)
            ->get();
    }

    public function selectedCustomer(): ?Customer
    {
        if (! $this->customerId) {
            return null;
        }

        return Customer::query()->find($this->customerId);
    }

    public function paymentMethods()
    {
        return PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function addToCart(string $productId, ?string $variantId = null): void
    {
        $product = Product::query()->with(['category', 'variants.values.attribute', 'variants.values.attributeValue'])->find($productId);

        if (! $product) {
            return;
        }

        if ($variantId) {
            $variant = $product->variants->where('is_active', true)->find($variantId);
            if ($variant) {
                $this->selectedVariantId = $variantId;
                $this->confirmVariantSelection();
                return;
            }
        }

        // If product has variants, open variant selector
        if ($product->has_variants) {
            $this->openVariantModal($productId);

            return;
        }

        if ($product->track_serial_numbers) {
            $this->openSerialModal($productId);

            return;
        }

        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
        $inventory = app(\App\Services\InventoryService::class);

        if ($product->track_stock && !$inventory->hasEnoughStock($product->id, $branchId, 1)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Out of stock',
                'description' => $product->name . ' has no available stock at this branch.',
            ]);

            return;
        }

        if (isset($this->cart[$productId])) {
            $this->increaseQuantity($productId);

            return;
        }

        $this->cart[$productId] = [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'category' => $product->category?->name ?? 'Uncategorized',
            'price' => (float) ($product->billing_price ?? $product->selling_price ?? 0),
            'cost_price' => (float) ($product->cost_price ?? 0),
            'tax_rate' => (float) ($product->tax_rate ?? 0),
            'stock' => $inventory->getProductStock($product->id, $branchId),
            'track_stock' => (bool) $product->track_stock,
            'quantity' => 1,
        ];
    }

    protected function getListeners(): array
    {
        return [
            'scanner-product-found' => 'onScannerProductFound',
        ];
    }

    public function onScannerProductFound(array $data): void
    {
        $productId = $data['product_id'];
        $variantId = $data['variant_id'] ?? null;

        $product = Product::find($productId);
        if (!$product) return;

        if ($product->track_serial_numbers) {
            $this->openSerialModal($productId);
            return;
        }

        $this->addToCart($productId, $variantId);
    }

    public function pollScanQueue(): void
    {
        $scans = \App\Models\ScanQueue::where('status', 'pending')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        foreach ($scans as $scan) {
            try {
                $barcode = trim($scan->barcode);
                $product = Product::where('barcode', $barcode)->first();

                if (!$product) {
                    $scan->update(['status' => 'failed', 'error_message' => 'Product not found', 'processed_at' => now()]);
                    continue;
                }

                if ($product->track_serial_numbers) {
                    $scan->update(['status' => 'processed', 'processed_at' => now()]);
                    $this->openSerialModal($product->id);
                } else {
                    $this->addToCart($product->id);
                    $scan->update(['status' => 'processed', 'processed_at' => now()]);
                }
            } catch (\Exception $e) {
                $scan->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'processed_at' => now()]);
            }
        }
    }

    public function openVariantModal(string $productId): void
    {
        $product = Product::query()
            ->with(['variants.values.attribute', 'variants.values.attributeValue'])
            ->find($productId);

        if (! $product || empty($product->variants)) {
            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
        $inventory = app(\App\Services\InventoryService::class);

        $this->variantModalProductId = $productId;
        $this->variantModalProductName = $product->name;
        $this->selectedVariantId = null;

        $this->variantModalVariants = $product->variants
            ->where('is_active', true)
            ->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'effective_selling_price' => (float) $v->effective_selling_price,
                'effective_cost_price' => (float) $v->effective_cost_price,
                'available_stock' => $v->track_serial_numbers
                    ? (float) $v->serials()->where('status', 'available')->where('branch_id', $branchId)->count()
                    : ($branchId ? $inventory->getProductStock($v->product_id, $branchId, $v->id) : (float) $v->available_stock),
                'track_serial_numbers' => $v->track_serial_numbers,
                'selling_price_mode' => $v->selling_price_mode,
                'combination' => $v->values
                    ->map(fn ($vv) => $vv->attribute->name . ': ' . $vv->attributeValue->value)
                    ->implode(' / '),
                'attributes' => $v->values->mapWithKeys(fn ($vv) => [
                    $vv->attribute->name => $vv->attributeValue->value,
                ])->toArray(),
            ])
            ->toArray();

        $this->showVariantModal = true;
    }

    public function selectVariant(string $variantId): void
    {
        $this->selectedVariantId = $variantId;
    }

    public function confirmVariantSelection(): void
    {
        if (empty($this->selectedVariantId)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'No variant selected',
                'description' => 'Select a variant to add to cart.',
            ]);

            return;
        }

        $variant = ProductVariant::query()
            ->with(['product', 'values.attribute', 'values.attributeValue'])
            ->find($this->selectedVariantId);

        if (! $variant || !$variant->is_active) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Variant not available',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $cartKey = 'variant_' . $variant->id;

        // If serialized, open serial modal for this variant
        if ($variant->track_serial_numbers) {
            $this->showVariantModal = false;
            $this->openSerialModalForVariant($variant);
            return;
        }

        // Check stock
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
        $inventory = app(\App\Services\InventoryService::class);
        $branchStock = $branchId ? $inventory->getProductStock($variant->product_id, $branchId, $variant->id) : (float) $variant->available_stock;

        if ($variant->track_stock && $branchStock <= 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Out of stock',
                'description' => $variant->name . ' has no available stock at this branch.',
            ]);

            return;
        }

        // Check if already in cart
        if (isset($this->cart[$cartKey])) {
            $this->increaseQuantity($cartKey);
            $this->showVariantModal = false;
            return;
        }

        $this->cart[$cartKey] = [
            'id' => $variant->product_id,
            'variant_id' => $variant->id,
            'name' => $variant->product->name,
            'variant_name' => $variant->name,
            'barcode' => $variant->barcode ?? $variant->product->barcode,
            'category' => $variant->product->category?->name ?? 'Uncategorized',
            'price' => (float) $variant->effective_selling_price,
            'cost_price' => (float) $variant->effective_cost_price,
            'tax_rate' => (float) ($variant->product->tax_rate ?? 0),
            'stock' => $branchStock,
            'track_stock' => true,
            'quantity' => 1,
        ];

        $this->showVariantModal = false;
        $this->selectedVariantId = null;
    }

    public function openSerialModalForVariant(ProductVariant $variant): void
    {
        $tenantId = auth()->user()?->tenant_id;

        $this->serialModalProductId = $variant->product_id;
        $this->serialModalProductName = $variant->product->name . ' — ' . $variant->name;
        $this->serialSearch = '';
        $this->serialModalSelected = [];

        $this->serialModalAvailable = ProductSerial::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $variant->product_id)
            ->where('variant_id', $variant->id)
            ->where('status', 'available')
            ->orderBy('serial_number')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'serial_number' => $s->serial_number,
                'box_serial_number' => $s->box_serial_number,
                'cost_price' => (float) ($s->cost_price ?: $variant->effective_cost_price),
                'selling_price' => (float) ($s->selling_price ?: $variant->effective_selling_price),
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
            ])
            ->toArray();

        $this->showSerialModal = true;
    }

    public function increaseQuantity(string $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $item = $this->cart[$productId];

        if (! empty($item['track_serial_numbers'])) {
            return;
        }

        $nextQuantity = ((float) $item['quantity']) + 1;

        if ($item['track_stock'] && $nextQuantity > (float) $item['stock']) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Stock limit reached',
                'description' => 'Only ' . number_format((float) $item['stock'], 2) . ' units available.',
            ]);

            return;
        }

        $this->cart[$productId]['quantity'] = $nextQuantity;
    }

    public function decreaseQuantity(string $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $nextQuantity = ((float) $this->cart[$productId]['quantity']) - 1;

        if ($nextQuantity <= 0) {
            $this->removeFromCart($productId);

            return;
        }

        $this->cart[$productId]['quantity'] = $nextQuantity;
    }

    public function removeFromCart(string $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function openSerialModal(string $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $tenantId = auth()->user()?->tenant_id;

        $this->serialModalProductId = $productId;
        $this->serialModalProductName = $product->name;
        $this->serialSearch = '';
        $this->serialModalSelected = [];

        $this->serialModalAvailable = ProductSerial::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->orderBy('serial_number')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'serial_number' => $s->serial_number,
                'box_serial_number' => $s->box_serial_number,
                'cost_price' => (float) ($s->cost_price ?: $product->cost_price),
                'selling_price' => (float) ($s->selling_price ?: $product->billing_price),
            ])
            ->toArray();

        $this->showSerialModal = true;
    }

    public function toggleSerial(string $serialId): void
    {
        $key = array_search($serialId, $this->serialModalSelected);

        if ($key === false) {
            $this->serialModalSelected[] = $serialId;
        } else {
            unset($this->serialModalSelected[$key]);
            $this->serialModalSelected = array_values($this->serialModalSelected);
        }
    }

    public function confirmSerialSelection(): void
    {
        if (empty($this->serialModalSelected)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'No serial selected',
                'description' => 'Select at least one serial number.',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $productId = $this->serialModalProductId;

        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $serials = ProductSerial::query()
            ->with('variant')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->whereIn('id', $this->serialModalSelected)
            ->get()
            ->keyBy('id');

        foreach ($this->serialModalSelected as $serialId) {
            $serial = $serials->get($serialId);

            if (! $serial) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'title' => 'Serial unavailable',
                    'description' => $serial?->serial_number . ' is no longer available.',
                ]);

                return;
            }

            $cartKey = $productId . '_serial_' . $serial->id;

            $this->cart[$cartKey] = [
                'id' => $product->id,
                'variant_id' => $serial->variant_id ?? null,
                'name' => $product->name,
                'variant_name' => $serial->variant?->name ?? null,
                'barcode' => $product->barcode,
                'category' => $product->category?->name ?? 'Uncategorized',
                'price' => (float) ($serial->selling_price ?: $product->billing_price ?? 0),
                'cost_price' => (float) ($serial->cost_price ?: $product->cost_price ?? 0),
                'tax_rate' => (float) ($product->tax_rate ?? 0),
                'stock' => 1,
                'track_stock' => false,
                'track_serial_numbers' => true,
                'quantity' => 1,
                'serial_id' => $serial->id,
                'serial_number' => $serial->serial_number,
            ];
        }

        $this->showSerialModal = false;
        $this->serialModalSelected = [];
        $this->serialModalProductId = null;
    }

    public function getFilteredSerialsProperty(): array
    {
        if ($this->serialSearch === '') {
            return $this->serialModalAvailable;
        }

        $search = strtolower($this->serialSearch);

        return array_values(array_filter(
            $this->serialModalAvailable,
            fn ($s) => str_contains(strtolower($s['serial_number']), $search)
                || str_contains(strtolower($s['box_serial_number'] ?? ''), $search),
        ));
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->orderDiscount = 0;
        $this->amountReceived = 0;
        $this->customerId = null;
        $this->splitPayment = false;
        $this->splitPayments = [];
        $this->paymentType = 'cash';
        $this->layawayDueDate = null;
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => (float) $item['price'] * (float) $item['quantity']);
    }

    public function getTaxAmountProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => ((float) $item['price'] * (float) $item['quantity']) * ((float) $item['tax_rate'] / 100));
    }

    public function getDiscountAmountProperty(): float
    {
        return max(0, min((float) ($this->orderDiscount ?? 0), $this->subtotal + $this->taxAmount));
    }

    public function getTotalAmountProperty(): float
    {
        return max(0, $this->subtotal + $this->taxAmount - $this->discountAmount);
    }

    public function getChangeDueProperty(): float
    {
        return max(0, $this->receivedAmount() - $this->totalAmount);
    }

    public function getSplitTotalProperty(): float
    {
        return collect($this->splitPayments)->sum(fn ($split) => (float) ($split['amount'] ?? 0));
    }

    public function receivedAmount(): float
    {
        if ($this->splitPayment && ! empty($this->splitPayments)) {
            return $this->splitTotal;
        }

        return (float) ($this->amountReceived ?? 0);
    }

    public function currency(): string
    {
        return auth()->user()?->tenant?->currency_code
            ?? auth()->user()?->tenant?->settings['currency']
            ?? 'UGX';
    }

    public function openOrdersModal(string $tab = 'paid'): void
    {
        $this->ordersTab = $tab;
        $this->showOrdersModal = true;
    }

    public function setOrdersTab(string $tab): void
    {
        $this->ordersTab = $tab;
    }

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Cart is empty',
                'description' => 'Add at least one product before payment.',
            ]);

            return;
        }

        $this->showPaymentModal = true;
    }

    public function setPaymentType(string $paymentType): void
    {
        if (! in_array($paymentType, ['cash', 'credit', 'layaway', 'wallet'], true)) {
            return;
        }

        if (! in_array($paymentType, ['cash'], true) && ! $this->selectedCustomer()) {
            return;
        }

        if ($paymentType === 'credit' && ! $this->selectedCustomer()?->credit_enabled) {
            return;
        }

        if ($paymentType === 'wallet') {
            $cust = $this->selectedCustomer();
            if (! $cust) return;
            $walletBalance = app(\App\Services\CustomerWalletService::class)->getBalance(auth()->user()->tenant_id, $cust->id);
            if ($walletBalance <= 0) {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'title' => 'No wallet balance',
                    'description' => 'This customer has no wallet funds.',
                ]);

                return;
            }
        }

        $this->paymentType = $paymentType;

        if ($paymentType === 'layaway' && ! $this->layawayDueDate) {
            $this->layawayDueDate = now()->addDays(30)->toDateString();
        }
    }

    public function toggleSplitPayment(): void
    {
        $this->splitPayment = ! $this->splitPayment;

        if ($this->splitPayment && empty($this->splitPayments)) {
            $this->splitPayments = [
                ['payment_method_id' => $this->paymentMethodId, 'amount' => $this->totalAmount],
            ];
        }
    }

    public function addSplitPaymentRow(): void
    {
        $this->splitPayments[] = [
            'payment_method_id' => $this->paymentMethodId,
            'amount' => null,
        ];
    }

    public function removeSplitPaymentRow(int $index): void
    {
        unset($this->splitPayments[$index]);

        $this->splitPayments = array_values($this->splitPayments);
    }

    public function updatedSplitPayments(mixed $value, ?string $key = null): void
    {
        if (count($this->splitPayments) < 2 || ! $key || ! str_ends_with($key, '.amount')) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        $count = count($this->splitPayments);

        $balanceIndex = ($index === $count - 1) ? 0 : $count - 1;

        $otherTotal = 0;

        foreach ($this->splitPayments as $i => $row) {
            if ($i === $balanceIndex) {
                continue;
            }

            $otherTotal += (float) ($row['amount'] ?? 0);
        }

        $this->splitPayments[$balanceIndex]['amount'] = max(0, round($this->totalAmount - $otherTotal, 2));
    }

    public function openAddCustomerModal(): void
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerTin = '';
        $this->newCustomerAddress = '';
        $this->showAddCustomerModal = true;
    }

    public function saveCustomer(): void
    {
        $name = trim($this->newCustomerName);

        if ($name === '') {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Customer name is required',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;

        if (! $tenantId) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Unable to add customer',
                'description' => 'No authenticated tenant was found.',
            ]);

            return;
        }

        $customerId = (string) Str::uuid();

        DB::table('customers')->insert([
            'id' => $customerId,
            'full_name' => $name,
            'phone' => $this->newCustomerPhone ?: null,
            'email' => $this->newCustomerEmail ?: null,
            'tin' => $this->newCustomerTin ?: null,
            'address' => $this->newCustomerAddress ?: null,
            'total_orders' => 0,
            'total_spent' => 0,
            'balance' => 0,
            'credit_limit' => 0,
            'credit_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false,
            'sync_status' => 'pending',
            'last_synced_at' => null,
            'tenant_id' => $tenantId,
        ]);

        $this->customerId = $customerId;
        $this->showAddCustomerModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Customer added',
            'description' => $name . ' was added and selected.',
        ]);
    }

    public function holdOrder(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Cart is empty',
                'description' => 'Add at least one product before holding.',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $userId = auth()->id();

        if (! $tenantId || ! $userId) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Unable to hold order',
                'description' => 'No authenticated tenant/user was found.',
            ]);

            return;
        }

        $session = $this->ensureSession($tenantId, $userId);
        $customer = $this->customerId ? Customer::query()->find($this->customerId) : null;

        try {
            DB::transaction(function () use ($tenantId, $userId, $customer, $session) {
                $heldNumber = 'HLD-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));

                $ctx = $this->registerContext();
                $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

                $transaction = Transaction::create([
                    'id' => (string) Str::uuid(),
                    'receipt_number' => $heldNumber,
                    'session_id' => $session->id,
                    'customer_id' => $customer?->id,
                    'customer_name' => $customer?->full_name ?? 'Walk-in Customer',
                    'user_id' => $userId,
                    'cashier_id' => $userId,
                    'register_name' => $ctx['register_name'],
                    'terminal_id' => $ctx['terminal_id'],
                    'subtotal' => $this->subtotal,
                    'tax_amount' => $this->taxAmount,
                    'discount_amount' => $this->discountAmount,
                    'total_amount' => $this->totalAmount,
                    'payment_method' => 'Hold',
                    'payment_method_id' => null,
                    'amount_paid' => 0,
                    'change_given' => 0,
                    'balance_due' => $this->totalAmount,
                    'status' => 'on_hold',
                    'settlement_status' => 'held',
                    'notes' => 'Order held at POS',
                    'transaction_date' => now(),
                    'order_name' => 'POS hold',
                    'document_type' => 'sale',
                    'transaction_type' => 'sale',
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'is_deleted' => false,
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                ]);

                foreach ($this->cart as $item) {
                    $lineSubtotal = (float) $item['price'] * (float) $item['quantity'];
                    $lineTax = $lineSubtotal * ((float) $item['tax_rate'] / 100);

                    DB::table('transaction_items')->insert([
                        'id' => (string) Str::uuid(),
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'service_id' => null,
                        'item_type' => 'product',
                        'product_name' => $item['name'] . (!empty($item['variant_name']) ? ' — ' . $item['variant_name'] : ''),
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'cost_price' => $item['cost_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $lineTax,
                        'discount' => 0,
                        'discount_applied' => 0,
                        'line_total' => $lineSubtotal,
                        'serial_id' => $item['serial_id'] ?? null,
                        'serial_number' => $item['serial_number'] ?? null,
                        'original_transaction_item_id' => null,
                        'sale_unit_id' => null,
                        'sale_unit_name' => null,
                        'sale_unit_label' => null,
                        'base_quantity' => null,
                        'unit_conversion_to_base' => 1,
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'is_deleted' => false,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'sync_status' => 'pending',
                        'last_synced_at' => null,
                    ]);
                }
            });

            $this->clearCart();
        $this->showOrdersModal = false;
        $this->renderKey++;

        $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Order held',
                'description' => 'The current order was saved and can be resumed later.',
            ]);
        } catch (\Throwable $exception) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Could not hold order',
                'description' => $exception->getMessage(),
            ]);
        }
    }

    public function resumeHeldOrder(string $transactionId): void
    {
        $held = Transaction::query()->where('id', $transactionId)->where('status', 'on_hold')->first();

        if (! $held) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Unpaid order not found',
            ]);

            return;
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $held->id)
            ->where('is_deleted', false)
            ->get();

        $this->cart = [];

        $productIds = $items->pluck('product_id')->unique()->values()->all();
        $products = Product::query()
            ->with(['category', 'variants.values.attribute', 'variants.values.attributeValue'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item->product_id);

            $cartVariantId = $item->variant_id ?? null;
            $variant = $cartVariantId ? $product?->variants?->find($cartVariantId) : null;

            $cartKey = ! empty($item->serial_id)
                ? $item->product_id . '_serial_' . $item->serial_id
                : ($cartVariantId ? 'variant_' . $cartVariantId : $item->product_id);

            $this->cart[$cartKey] = [
                'id' => $item->product_id,
                'variant_id' => $cartVariantId,
                'name' => $product?->name ?? $item->product_name,
                'variant_name' => $variant?->name ?? null,
                'barcode' => $variant?->barcode ?? $product?->barcode,
                'category' => $product?->category?->name ?? 'Uncategorized',
                'price' => $variant ? (float) $variant->effective_selling_price : (float) ($product?->billing_price ?? $product?->selling_price ?? $item->unit_price),
                'cost_price' => $variant ? (float) $variant->effective_cost_price : (float) ($item->cost_price ?? 0),
                'tax_rate' => (float) ($item->tax_rate ?? 0),
                'stock' => $variant ? (float) ($variant->track_serial_numbers ? $variant->serials()->where('status', 'available')->count() : app(\App\Services\InventoryService::class)->getProductStock($product?->id, $item->branch_id ?? '', $cartVariantId)) : (float) app(\App\Services\InventoryService::class)->getProductStock($item->product_id, $item->branch_id ?? ''),
                'track_stock' => $variant ? true : (bool) ($product?->track_stock ?? false),
                'track_serial_numbers' => ! empty($item->serial_id) || ($variant && $variant->track_serial_numbers),
                'quantity' => (float) $item->quantity,
                'serial_id' => $item->serial_id ?? null,
                'serial_number' => $item->serial_number ?? null,
            ];
        }

        $held->update(['status' => 'resumed', 'settlement_status' => 'resumed']);

        $this->showOrdersModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Order resumed',
            'description' => $held->receipt_number . ' was restored to the cart.',
        ]);
    }

    public function voidHeldOrder(string $transactionId): void
    {
        $held = Transaction::query()->where('id', $transactionId)->where('status', 'on_hold')->first();

        if (! $held) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Held order not found',
            ]);

            return;
        }

        $held->update(['status' => 'voided', 'settlement_status' => 'voided', 'is_deleted' => true]);

        DB::table('transaction_items')
            ->where('transaction_id', $held->id)
            ->update(['is_deleted' => true]);

        $this->dispatch('toast', [
            'type' => 'error',
            'title' => 'Held order voided',
            'description' => $held->receipt_number . ' was voided.',
        ]);
    }

    public function collectOrderPayment(string $transactionId): void
    {
        $amount = null;

        if (isset($this->collectAmounts[$transactionId]) && filled($this->collectAmounts[$transactionId])) {
            $amount = (float) $this->collectAmounts[$transactionId];
        }

        $this->collectUnpaidOrderPayment($transactionId, $amount);
        unset($this->collectAmounts[$transactionId]);
    }

    public function collectUnpaidOrderPayment(string $transactionId, ?float $amount = null): void
    {
        $order = Transaction::query()
            ->where('id', $transactionId)
            ->where('settlement_status', 'partial')
            ->first();

        if (! $order) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Held order not found',
            ]);

            return;
        }

        if ($order->customer_id && ! (bool) (auth()->user()?->hasPermission('can_settle_credit_payments') ?? false)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Permission required',
                'description' => 'You need the Credit Payments permission to settle unpaid orders.',
            ]);

            return;
        }

        $due = max(0, (float) $order->total_amount - (float) $order->amount_paid);

        if ($due <= 0) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'No balance due',
                'description' => 'This order is already fully paid.',
            ]);

            return;
        }

        $collectAmount = $amount !== null ? (float) $amount : $due;

        if ($collectAmount <= 0 || $collectAmount > $due) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Invalid amount',
                'description' => 'Enter an amount between 1 and ' . number_format($due, 0) . '.',
            ]);

            return;
        }

        $paymentMethod = $this->paymentMethodId
            ? PaymentMethod::query()->find($this->paymentMethodId)
            : PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first();

        $reference = 'PAY-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

        $paymentPayload = [
            'id' => (string) Str::uuid(),
            'transaction_id' => $order->id,
            'payment_method_id' => $paymentMethod?->id,
            'payment_method' => $paymentMethod?->name ?? 'Cash',
            'amount_paid' => $collectAmount,
            'reference_number' => $reference,
            'payment_date' => now(),
            'session_id' => $order->session_id,
            'tenant_id' => $order->tenant_id,
            'branch_id' => $branchId,
            'is_deleted' => false,
            'sort_order' => 0,
            'sync_status' => 'pending',
            'last_synced_at' => null,
        ];

        if (Schema::hasColumn('transaction_payments', 'created_at')) {
            $paymentPayload['created_at'] = now();
        }

        if (Schema::hasColumn('transaction_payments', 'updated_at')) {
            $paymentPayload['updated_at'] = now();
        }

        try {
            DB::transaction(function () use ($order, $collectAmount, $paymentPayload, $reference) {
                $newPaid = (float) $order->amount_paid + $collectAmount;
                $newDue = max(0, (float) $order->total_amount - $newPaid);
                $fullyPaid = $newDue <= 0;

                $order->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newDue,
                    'change_given' => 0,
                    'status' => $fullyPaid ? 'completed' : 'partial',
                    'settlement_status' => $fullyPaid ? 'paid' : 'partial',
                ]);

                DB::table('transaction_payments')->insert($paymentPayload);

                if ($order->customer_id) {
                    $isLayaway = DB::table('layaway_plans')->where('transaction_id', $order->id)->exists();

                    $this->writeLedgerEntry(
                        $order->customer_id,
                        $order->id,
                        $isLayaway ? 'layaway_payment' : 'credit_payment',
                        -$collectAmount,
                        'Payment ' . $reference . ' for ' . $order->receipt_number,
                        $order->tenant_id
                    );

                    if ($isLayaway) {
                        DB::table('layaway_plans')
                            ->where('transaction_id', $order->id)
                            ->update([
                                'amount_paid' => $newPaid,
                                'status' => $fullyPaid ? 'completed' : 'active',
                                'is_collected' => $fullyPaid,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });
        } catch (\Throwable $exception) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Payment failed',
                'description' => $exception->getMessage(),
            ]);

            return;
        }

        $this->showOrdersModal = false;
        $this->renderKey++;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Payment collected',
            'description' => $order->receipt_number . ' now has ' . number_format(max(0, (float) $order->balance_due - $collectAmount), 0) . ' remaining.',
        ]);
    }

    public function receipt(): ?array
    {
        if (! $this->lastTransactionId) {
            return null;
        }

        $transaction = Transaction::query()
            ->with(['branch', 'tenant.business', 'user'])
            ->where('id', $this->lastTransactionId)
            ->first();

        if (! $transaction) {
            return null;
        }

        $tenant = $transaction->tenant;
        $business = $tenant?->business;

        return [
            'transaction' => $transaction,
            'items' => DB::table('transaction_items')
                ->where('transaction_id', $transaction->id)
                ->where('is_deleted', false)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->all(),
            'payments' => DB::table('transaction_payments')
                ->where('transaction_id', $transaction->id)
                ->where('is_deleted', false)
                ->orderBy('sort_order')
                ->get()
                ->all(),
            'customer' => $transaction->customer_id
                ? DB::table('customers')->where('id', $transaction->customer_id)->first()
                : null,
            'branch' => $transaction->branch?->name,
            'company' => [
                'name' => $business?->name ?? $tenant?->name ?? 'HALIS',
                'address' => $business?->address,
                'phone' => $business?->phone ?? $tenant?->admin_phone,
                'tin' => $business?->tin,
                'currency' => $business?->currency_code ?? $tenant?->currency_code ?? 'UGX',
            ],
            'cashier' => $transaction->user?->full_name ?? $transaction->user?->name ?? 'Cashier',
        ];
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->lastTransactionId = null;
    }

    public function orders(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchService = app(\App\Services\BranchService::class);
        $canViewAll = (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
        $activeBranchId = $canViewAll ? null : $branchService->getActiveBranchId();

        $all = Transaction::query()
            ->withCount(['items as item_count' => fn ($q) => $q->where('is_deleted', false)])
            ->where('tenant_id', $tenantId)
            ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            ->orderByDesc('transaction_date')
            ->limit(50)
            ->get()
            ->map(fn (Transaction $tx) => [
                'id' => $tx->id,
                'receipt_number' => $tx->receipt_number,
                'customer_name' => $tx->customer_name ?? 'Walk-in Customer',
                'total_amount' => (float) $tx->total_amount,
                'amount_paid' => (float) $tx->amount_paid,
                'balance_due' => (float) $tx->balance_due,
                'status' => $tx->status,
                'settlement_status' => $tx->settlement_status,
                'transaction_date' => $tx->transaction_date,
                'item_count' => (int) $tx->item_count,
            ]);

        return [
            'paid' => $all->filter(fn ($tx) => $tx['settlement_status'] === 'paid'),
            'unpaid' => $all->filter(fn ($tx) => $tx['settlement_status'] === 'partial' && $tx['balance_due'] > 0),
            'held' => $all->filter(fn ($tx) => $tx['status'] === 'on_hold'),
        ];
    }

    private function writeLedgerEntry(string $customerId, ?string $transactionId, string $type, float $amount, string $description, ?string $tenantId = null): void
    {
        $tenantId ??= auth()->user()?->tenant_id;
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
        $date = now();

        DB::transaction(function () use ($customerId, $transactionId, $type, $amount, $description, $tenantId, $branchId, $date) {
            $lastEntry = DB::table('customer_ledger')
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $balanceAfter = $lastEntry ? (float) $lastEntry->balance_after + $amount : $amount;

            DB::table('customer_ledger')->insert([
                'id' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'transaction_id' => $transactionId,
                'type' => $type,
                'description' => $description,
                'amount' => round($amount, 2),
                'balance_after' => round($balanceAfter, 2),
                'date' => $date,
                'created_at' => $date,
                'sync_status' => 'pending',
                'last_synced_at' => null,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]);

            DB::table('customers')
                ->where('id', $customerId)
                ->update(['balance' => round($balanceAfter, 2), 'updated_at' => $date]);
        });
    }

    private function registerContext(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        if (! $tenantId) {
            return ['register_name' => null, 'terminal_id' => null];
        }

        $device = DB::table('pos_devices')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('device_name')
            ->first();

        $register = DB::table('registers')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($device, fn ($q) => $q->where('id', $device->register_id))
            ->orderBy('name')
            ->first();

        return [
            'register_name' => $register?->name ?? null,
            'terminal_id' => $device?->id ?? null,
        ];
    }

    private function ensureSession(string $tenantId, string|int $userId): object
    {
        $session = DB::table('pos_sessions')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->whereNull('closed_at')
            ->orderByDesc('opened_at')
            ->first();

        if ($session) {
            return $session;
        }

        $sessionId = (string) Str::uuid();
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

        DB::table('pos_sessions')->insert([
            'id' => $sessionId,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'opened_at' => now(),
            'starting_cash' => 0,
            'expected_cash' => 0,
            'actual_cash' => 0,
            'card_payments' => 0,
            'discrepancy' => 0,
            'total_transactions' => 0,
            'total_items_sold' => 0,
            'total_refunds' => 0,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
            'sync_status' => 'pending',
            'last_synced_at' => null,
            'closed_by' => null,
            'closing_note' => null,
        ]);

        return (object) ['id' => $sessionId];
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Cart is empty',
                'description' => 'Add at least one product before checkout.',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $userId = auth()->id();

        if (! $tenantId || ! $userId) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Unable to checkout',
                'description' => 'No authenticated tenant/user was found.',
            ]);

            return;
        }

        $session = $this->ensureSession($tenantId, $userId);

        $paymentMethod = $this->paymentMethodId
            ? PaymentMethod::query()->find($this->paymentMethodId)
            : PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first();

        $customer = $this->customerId ? Customer::query()->find($this->customerId) : null;

        if ($this->paymentType !== 'cash') {
            if (! $customer) {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'title' => 'Customer required',
                    'description' => 'A customer is required for credit and layaway sales.',
                ]);

                return;
            }

            if ($this->paymentType === 'credit' && ! $customer->credit_enabled) {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'title' => 'Credit is not enabled',
                    'description' => 'This customer is not allowed to buy on credit.',
                ]);

                return;
            }
        }

        $payments = [];

        if ($this->splitPayment && ! empty($this->splitPayments)) {
            foreach ($this->splitPayments as $split) {
                $method = ! empty($split['payment_method_id'])
                    ? PaymentMethod::query()->find($split['payment_method_id'])
                    : null;
                $amount = (float) ($split['amount'] ?? 0);

                if ($amount > 0) {
                    $payments[] = [
                        'method' => $method,
                        'amount' => $amount,
                    ];
                }
            }
        }

        if (empty($payments)) {
            $method = $this->paymentMethodId
                ? PaymentMethod::query()->find($this->paymentMethodId)
                : PaymentMethod::query()->where('is_active', true)->orderBy('sort_order')->first();

            $received = $this->receivedAmount();

            $amount = match ($this->paymentType) {
                'credit', 'layaway' => $received,
                'wallet' => $this->totalAmount,
                default => $received > 0 ? $received : $this->totalAmount,
            };

            if ($amount > 0) {
                $payments[] = [
                    'method' => $method,
                    'amount' => $amount,
                ];
            }
        }

        $amountPaid = collect($payments)->sum('amount');

        $creditOverridden = false;

        try {
            $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
            $inventory = app(\App\Services\InventoryService::class);

            DB::transaction(function () use ($tenantId, $userId, $payments, $amountPaid, $customer, $session, &$creditOverridden, $branchId, $inventory) {
                if ($this->paymentType === 'credit' && $customer) {
                    $creditLimit = (float) ($customer->credit_limit ?? 0);
                    $currentOutstanding = (float) ($customer->balance ?? 0);
                    $newExposure = $currentOutstanding + (float) $this->totalAmount;

                    if ($creditLimit > 0 && $newExposure > $creditLimit) {
                        if (! (bool) (auth()->user()?->hasPermission('can_override_credit_limit') ?? false)) {
                            throw new \RuntimeException(
                                'Credit limit exceeded. Current outstanding ' . number_format($currentOutstanding, 0) . ' plus this sale ' . number_format((float) $this->totalAmount, 0) . ' exceeds the limit of ' . number_format($creditLimit, 0) . '.'
                            );
                        }

                        $creditOverridden = true;
                    }
                }

                foreach ($this->cart as $item) {
                    $product = Product::query()->lockForUpdate()->find($item['id']);

                    if (! $product) {
                        throw new \RuntimeException($item['name'] . ' no longer exists.');
                    }

                    $cartVariantId = $item['variant_id'] ?? null;

                    if (! empty($item['track_serial_numbers'])) {
                        if (! empty($item['serial_id'])) {
                            $serial = ProductSerial::query()
                                ->where('id', $item['serial_id'])
                                ->where('status', 'available')
                                ->lockForUpdate()
                                ->first();

                            if (! $serial) {
                                throw new \RuntimeException($item['serial_number'] . ' is no longer available.');
                            }
                        } else {
                            throw new \RuntimeException($product->name . ' requires a serial number.');
                        }
                    } elseif ($cartVariantId) {
                        $variant = ProductVariant::query()->lockForUpdate()->find($cartVariantId);
                        if ($variant && $variant->track_serial_numbers === false && !$inventory->hasEnoughStock($product->id, $branchId, (float) $item['quantity'], $cartVariantId)) {
                            throw new \RuntimeException($item['variant_name'] . ' does not have enough stock at this branch.');
                        }
                    } elseif ($product->track_stock && !$inventory->hasEnoughStock($product->id, $branchId, (float) $item['quantity'])) {
                        throw new \RuntimeException($product->name . ' does not have enough stock at this branch.');
                    }
                }

                $receiptNumber = 'POS-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
                $primaryPayment = $payments[0] ?? ['method' => null, 'amount' => 0];

                $ctx = $this->registerContext();

                $dueDate = match ($this->paymentType) {
                    'layaway' => $this->layawayDueDate ? \Illuminate\Support\Carbon::parse($this->layawayDueDate) : now()->addDays(30),
                    'credit' => now()->addDays(30),
                    default => null,
                };

                $transaction = Transaction::create([
                    'id' => (string) Str::uuid(),
                    'receipt_number' => $receiptNumber,
                    'session_id' => $session->id,
                    'customer_id' => $customer?->id,
                    'customer_name' => $customer?->full_name ?? 'Walk-in Customer',
                    'user_id' => $userId,
                    'cashier_id' => $userId,
                    'register_name' => $ctx['register_name'],
                    'terminal_id' => $ctx['terminal_id'],
                    'subtotal' => $this->subtotal,
                    'tax_amount' => $this->taxAmount,
                    'discount_amount' => $this->discountAmount,
                    'total_amount' => $this->totalAmount,
                    'payment_method_id' => $primaryPayment['method']?->id,
                    'payment_method' => $primaryPayment['method']?->name ?? 'Cash',
                    'amount_paid' => $amountPaid,
                    'change_given' => max(0, $amountPaid - $this->totalAmount),
                    'balance_due' => max(0, $this->totalAmount - $amountPaid),
                    'status' => $amountPaid >= $this->totalAmount ? 'completed' : 'partial',
                    'settlement_status' => $amountPaid >= $this->totalAmount ? 'paid' : 'partial',
                    'notes' => ($this->paymentType === 'credit' ? 'Credit sale' : ($this->paymentType === 'layaway' ? 'Layaway sale' : ($this->paymentType === 'wallet' ? 'Wallet sale' : 'POS sale'))) . ($creditOverridden ? ' (credit limit overridden)' : ''),
                    'transaction_date' => now(),
                    'due_date' => $dueDate,
                    'order_name' => 'POS sale',
                    'document_type' => 'sale',
                    'transaction_type' => match ($this->paymentType) {
                        'credit' => 'credit',
                        'layaway' => 'layaway',
                        default => 'sale',
                    },
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'is_deleted' => false,
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                ]);

                foreach ($this->cart as $item) {
                    $lineSubtotal = (float) $item['price'] * (float) $item['quantity'];
                    $lineTax = $lineSubtotal * ((float) $item['tax_rate'] / 100);

                    $cartSerialId = $item['serial_id'] ?? null;
                    $cartSerialNumber = $item['serial_number'] ?? null;
                    $cartVariantId = $item['variant_id'] ?? null;

                    DB::table('transaction_items')->insert([
                        'id' => (string) Str::uuid(),
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'variant_id' => $cartVariantId,
                        'service_id' => null,
                        'item_type' => 'product',
                        'product_name' => $item['name'] . ($item['variant_name'] ?? '' ? ' — ' . $item['variant_name'] : ''),
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'cost_price' => $item['cost_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $lineTax,
                        'discount' => 0,
                        'discount_applied' => 0,
                        'line_total' => $lineSubtotal,
                        'serial_id' => $cartSerialId,
                        'serial_number' => $cartSerialNumber,
                        'original_transaction_item_id' => null,
                        'sale_unit_id' => null,
                        'sale_unit_name' => null,
                        'sale_unit_label' => null,
                        'base_quantity' => null,
                        'unit_conversion_to_base' => 1,
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'is_deleted' => false,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'sync_status' => 'pending',
                        'last_synced_at' => null,
                    ]);

                    if ($cartSerialId) {
                        ProductSerial::query()
                            ->where('id', $cartSerialId)
                            ->where('tenant_id', $tenantId)
                            ->update([
                                'status' => 'sold',
                                'sold_at' => now(),
                                'sale_id' => $transaction->id,
                                'updated_at' => now(),
                            ]);
                    }

                    $movementPayload = [
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'product_id' => $item['id'],
                        'variant_id' => $cartVariantId,
                        'serial_id' => $cartSerialId,
                        'serial_number' => $cartSerialNumber,
                        'movement_type' => 'out',
                        'quantity' => $item['quantity'],
                        'sale_quantity' => $item['quantity'],
                        'base_quantity' => $item['quantity'],
                        'reference_id' => $transaction->id,
                        'reason' => 'POS sale ' . $receiptNumber,
                        'user_id' => $userId,
                        'movement_date' => now(),
                    ];

                    StockMovement::create($movementPayload);
                }

                foreach ($payments as $index => $payment) {
                    $paymentPayload = [
                        'id' => (string) Str::uuid(),
                        'transaction_id' => $transaction->id,
                        'payment_method_id' => $payment['method']?->id,
                        'payment_method' => $payment['method']?->name ?? 'Cash',
                        'amount_paid' => $payment['amount'],
                        'reference_number' => 'PAY-' . now()->format('YmdHis') . '-' . ($index + 1),
                        'payment_date' => now(),
                        'session_id' => $session->id,
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'is_deleted' => false,
                        'sort_order' => $index,
                        'sync_status' => 'pending',
                        'last_synced_at' => null,
                    ];

                    if (Schema::hasColumn('transaction_payments', 'created_at')) {
                        $paymentPayload['created_at'] = now();
                    }

                    if (Schema::hasColumn('transaction_payments', 'updated_at')) {
                        $paymentPayload['updated_at'] = now();
                    }

                    DB::table('transaction_payments')->insert($paymentPayload);
                }

                if ($customer && (float) $transaction->balance_due > 0) {
                    $this->writeLedgerEntry(
                        $customer->id,
                        $transaction->id,
                        $this->paymentType === 'layaway' ? 'layaway' : 'credit',
                        (float) $transaction->balance_due,
                        ($this->paymentType === 'layaway' ? 'Layaway sale' : 'Credit sale') . ' ' . $receiptNumber,
                        $tenantId
                    );

                    if ($this->paymentType === 'layaway') {
                        DB::table('layaway_plans')->insert([
                            'id' => (string) Str::uuid(),
                            'transaction_id' => $transaction->id,
                            'customer_id' => $customer->id,
                            'total_amount' => (float) $transaction->total_amount,
                            'amount_paid' => (float) $transaction->amount_paid,
                            'status' => 'active',
                            'due_date' => $this->layawayDueDate ?? now()->addDays(30)->toDateString(),
                            'is_collected' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'sync_status' => 'pending',
                            'last_synced_at' => null,
                            'tenant_id' => $tenantId,
                            'branch_id' => $branchId,
                        ]);
                    }
                }

                if ($this->paymentType === 'wallet' && $customer) {
                    $walletAmount = min((float) $this->totalAmount, $amountPaid);
                    if ($walletAmount > 0) {
                        app(\App\Services\CustomerWalletService::class)->useFunds(
                            $tenantId,
                            $customer->id,
                            $walletAmount,
                            $branchId,
                            'transaction',
                            $transaction->id,
                            "Wallet payment for {$receiptNumber}"
                        );
                    }
                }

                $this->lastReceiptNumber = $receiptNumber;
                $this->lastTransactionId = $transaction->id;
            });

            Cache::forget("tenant:{$tenantId}:widget_stats");
            Cache::forget("tenant:{$tenantId}:widget_revenue");
            Cache::forget("tenant:{$tenantId}:widget_sales_dashboard");

            if ($customer && $this->paymentType !== 'credit') {
                try {
                    $points = (int) floor((float) $this->totalAmount / 1000);
                    if ($points > 0) {
                        $loyaltyBranchId = app(\App\Services\BranchService::class)->getActiveBranchId();
                        app(\App\Services\CustomerLoyaltyService::class)->earnPoints(
                            $tenantId,
                            $customer->id,
                            $points,
                            $loyaltyBranchId,
                            'transaction',
                            $this->lastTransactionId,
                            "Earned {$points} points from sale"
                        );
                    }
                } catch (\Throwable $e) {
                    // Loyalty is non-critical
                }
            }

            $receiptNumber = $this->lastReceiptNumber;
            $this->clearCart();
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;
            $this->renderKey++;

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Sale completed',
                'description' => 'Receipt ' . $receiptNumber . ' was saved successfully.',
                'button' => [
                    'title' => 'Print Receipt',
                    'js' => "window.print()",
                ],
            ]);
        } catch (\Throwable $exception) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Checkout failed',
                'description' => $exception->getMessage(),
            ]);
        }
    }

    public function openCashInModal(): void
    {
        $this->cashMovementType = 'cash_in';
        $this->cashAmount = '';
        $this->cashReason = '';
        $this->showCashModal = true;
    }

    public function openCashOutModal(): void
    {
        $this->cashMovementType = 'cash_out';
        $this->cashAmount = '';
        $this->cashReason = '';
        $this->showCashModal = true;
    }

    public function saveCashMovement(): void
    {
        $amount = (float) $this->cashAmount;

        if ($amount <= 0) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Invalid amount',
                'description' => 'Enter an amount greater than zero.',
            ]);

            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $userId = auth()->id();
        $session = $this->ensureSession($tenantId, $userId);

        try {
            $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

            DB::table('cash_movements')->insert([
                'id' => (string) Str::uuid(),
                'session_id' => $session->id,
                'user_id' => $userId,
                'branch_id' => $branchId,
                'movement_type' => $this->cashMovementType,
                'amount' => $amount,
                'reason' => $this->cashReason ?: null,
                'category' => $this->cashMovementType === 'cash_in' ? 'Cash In' : 'Cash Out',
                'movement_date' => now(),
                'created_at' => now(),
                'sync_status' => 'pending',
                'last_synced_at' => null,
                'tenant_id' => $tenantId,
            ]);

            DB::table('cash_drawer_logs')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'action' => $this->cashMovementType,
                'timestamp' => now(),
                'sync_status' => 'pending',
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]);

            $this->showCashModal = false;
            $this->cashAmount = '';
            $this->cashReason = '';
            $this->renderKey++;

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => $this->cashMovementType === 'cash_in' ? 'Cash In recorded' : 'Cash Out recorded',
                'description' => number_format($amount, 0) . ' recorded successfully.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Failed',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function openXReport(): void
    {
        $this->reportType = 'x';
        $this->showReportModal = true;
    }

    public function openZReport(): void
    {
        $this->reportType = 'z';
        $this->zActualCash = '';
        $this->zNote = '';
        $this->shortage = 0;
        $this->showReportModal = true;
    }

    public function updatedZActualCash(): void
    {
        $this->shortage = $this->computeShortage();
    }

    public function computeShortage(): float
    {
        $data = $this->reportData();
        $actual = (float) $this->zActualCash;

        return $actual - $data['expectedCash'];
    }

    public function generateZReport(): void
    {
        $data = $this->reportData();
        $actualCash = (float) $this->zActualCash;
        $shortage = $actualCash - $data['expectedCash'];

        try {
            DB::table('pos_sessions')->where('id', $data['session']->id)->update([
                'closed_at' => now(),
                'expected_cash' => $data['expectedCash'],
                'actual_cash' => $actualCash,
                'card_payments' => $data['cardPayments'],
                'discrepancy' => $shortage,
                'total_transactions' => $data['count'],
                'total_items_sold' => $data['items'],
                'total_refunds' => $data['totalRefunds'],
                'status' => 'closed',
                'closed_by' => auth()->id(),
                'closing_note' => $this->zNote ?: 'Z report generated at ' . now()->format('H:i'),
                'updated_at' => now(),
            ]);

            $this->showReportModal = false;
            $this->zActualCash = '';
            $this->zNote = '';

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Z Report generated',
                'description' => $shortage > 0 ? 'Session closed. Overage: ' . number_format($shortage) . '.' : ($shortage < 0 ? 'Session closed. Shortage: ' . number_format(abs($shortage)) . '.' : 'Session closed. Register balanced.'),
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Failed to close session',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function reportData(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $userId = auth()->id();
        $session = $this->ensureSession($tenantId, $userId);
        $currency = $this->currency();

        $sales = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('session_id', $session->id)
            ->where('document_type', 'sale')
            ->where('is_deleted', false)
            ->get();

        $refunds = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('session_id', $session->id)
            ->where('document_type', 'refund')
            ->where('is_deleted', false)
            ->get();

        $payments = DB::table('transaction_payments')
            ->where('tenant_id', $tenantId)
            ->where('session_id', $session->id)
            ->where('is_deleted', false)
            ->get();

        $movements = DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->where('session_id', $session->id)
            ->get();

        $byMethod = $payments->groupBy('payment_method')->map(fn ($rows) => $rows->sum('amount_paid'));

        $grossSales = (float) $sales->sum('total_amount');
        $totalDiscounts = (float) $sales->sum('discount_amount');
        $totalTaxes = (float) $sales->sum('tax_amount');
        $totalRefunds = (float) $refunds->sum('total_amount');
        $netSales = $grossSales - $totalDiscounts + $totalTaxes;
        $cashPayments = (float) ($byMethod['Cash'] ?? 0);
        $cardPayments = (float) ($byMethod['Card'] ?? 0);
        $mobilePayments = (float) ($byMethod['Mobile Money'] ?? 0);
        $bankPayments = (float) ($byMethod['Bank Transfer'] ?? 0);
        $count = $sales->count();

        $itemCount = 0;
        foreach ($sales as $sale) {
            $itemCount += (int) DB::table('transaction_items')
                ->where('transaction_id', $sale->id)
                ->where('is_deleted', false)
                ->sum('quantity');
        }

        $startingCash = (float) $session->starting_cash;
        $cashIn = (float) $movements->where('movement_type', 'cash_in')->sum('amount');
        $cashOut = (float) $movements->where('movement_type', 'cash_out')->sum('amount');
        $cashRefunds = $totalRefunds;
        $cashSales = $cashPayments;
        $expectedCash = $startingCash + $cashSales - $cashRefunds + $cashIn - $cashOut;

        return [
            'session' => $session,
            'opened_at' => $session->opened_at,
            'currency' => $currency,
            'grossSales' => $grossSales,
            'totalDiscounts' => $totalDiscounts,
            'totalTaxes' => $totalTaxes,
            'totalRefunds' => $totalRefunds,
            'netSales' => $netSales,
            'count' => $count,
            'items' => $itemCount,
            'cashPayments' => $cashPayments,
            'cardPayments' => $cardPayments,
            'mobilePayments' => $mobilePayments,
            'bankPayments' => $bankPayments,
            'startingCash' => $startingCash,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'cashRefunds' => $cashRefunds,
            'cashSales' => $cashSales,
            'expectedCash' => $expectedCash,
        ];
    }
}
