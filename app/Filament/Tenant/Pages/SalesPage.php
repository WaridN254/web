<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use BackedEnum;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\StockMovement;

class SalesPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.sales');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.sales_history');
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $title = 'Sales History';

    protected string $view = 'filament.tenant.pages.sales-page';

    protected function canDeleteSales(): bool
    {
        return (bool) (auth()->user()?->role?->can_delete_sales ?? false);
    }

    public function currency(): string
    {
        return auth()->user()?->tenant?->currency_code
            ?? auth()->user()?->tenant?->settings['currency']
            ?? 'UGX';
    }

    // Refund modal state
    public bool $showRefundModal = false;

    public string $refundSaleId = '';

    public string $refundReceiptNumber = '';

    public string $refundPaymentType = 'cash';

    public string $refundReason = '';

    public float $refundTotal = 0;

    public bool $refundSelectAll = true;

    public array $refundSaleData = [];

    public array $refundItems = [];

    // Receipt modal state
    public bool $showReceiptModal = false;

    public ?array $receiptData = null;

    public string $receiptType = 'sale';

    // Send email modal state
    public bool $showSendEmailModal = false;
    public string $sendEmailDocType = 'receipt';
    public string $sendEmailDocId = '';
    public string $sendEmailTo = '';
    public string $sendEmailSubject = '';
    public string $sendEmailMessage = '';
    public bool $sendEmailSending = false;
    public string $sendEmailResult = '';
    public bool $sendEmailSuccess = false;

    public function openRefundModal(string $saleId): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $sale = DB::table('transactions')
            ->where('id', $saleId)
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->first();

        if (!$sale) {
            return;
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $saleId)
            ->where('is_deleted', false)
            ->get();

        $this->refundSaleId = $saleId;
        $this->refundReceiptNumber = $sale->receipt_number ?? 'REF-' . now()->format('YmdHi') . '-' . strtoupper(Str::random(4));
        $this->refundPaymentType = strtolower($sale->payment_method) === 'card' ? 'card' : (strtolower($sale->payment_method) === 'bank transfer' ? 'check' : 'cash');
        $this->refundReason = '';
        $this->refundSelectAll = true;

        $this->refundSaleData = [
            'receipt_number' => $sale->receipt_number,
            'customer_name' => $sale->customer_name ?? 'Walk-in',
            'total_amount' => (float) $sale->total_amount,
            'payment_method' => $sale->payment_method,
            'transaction_date' => $sale->transaction_date,
        ];

        $this->refundItems = $items->map(fn ($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => (float) $item->line_total,
            'selected' => true,
            'condition' => 'resalable',
            'serial_id' => $item->serial_id,
            'serial_number' => $item->serial_number,
            'original_transaction_item_id' => $item->id,
            'sale_unit_id' => $item->sale_unit_id,
            'sale_unit_name' => $item->sale_unit_name ?? '',
            'base_quantity' => $item->base_quantity,
            'cost_price' => $item->cost_price,
        ])->toArray();

        $this->refundTotal = collect($this->refundItems)->where('selected', true)->sum('line_total');
        $this->showRefundModal = true;
    }

    public function toggleRefundSelectAll(): void
    {
        $this->refundSelectAll = !$this->refundSelectAll;
        foreach ($this->refundItems as $key => &$item) {
            $item['selected'] = $this->refundSelectAll;
        }
        unset($item);
        $this->refundTotal = collect($this->refundItems)->where('selected', true)->sum('line_total');
    }

    public function toggleRefundItem(int $index): void
    {
        $this->refundItems[$index]['selected'] = !$this->refundItems[$index]['selected'];
        $this->refundSelectAll = collect($this->refundItems)->every('selected', true);
        $this->refundTotal = collect($this->refundItems)->where('selected', true)->sum('line_total');
    }

    public function setRefundCondition(int $index, string $condition): void
    {
        $this->refundItems[$index]['condition'] = $condition;
    }

    public function setRefundPaymentType(string $type): void
    {
        $this->refundPaymentType = $type;
    }

    public function processRefund(): void
    {
        $selectedItems = collect($this->refundItems)->where('selected', true);

        if ($selectedItems->isEmpty()) {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'No items selected']);
            return;
        }

        if ($this->refundTotal <= 0) {
            $this->dispatch('toast', ['type' => 'warning', 'title' => 'Invalid refund amount']);
            return;
        }

        $sale = DB::table('transactions')->where('id', $this->refundSaleId)->first();
        if (!$sale) {
            $this->dispatch('toast', ['type' => 'error', 'title' => 'Sale not found']);
            return;
        }

        try {
            DB::transaction(function () use ($sale, $selectedItems) {
                $tenantId = $sale->tenant_id;
                $userId = auth()->id();
                $reference = 'REF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

                $paymentMethodName = match ($this->refundPaymentType) {
                    'card' => 'Card',
                    'check' => 'Bank Transfer',
                    default => 'Cash',
                };

                $paymentMethod = DB::table('payment_methods')
                    ->where('tenant_id', $tenantId)
                    ->where('name', $paymentMethodName)
                    ->first();

                $refundId = (string) Str::uuid();
                $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

                DB::table('transactions')->insert([
                    'id' => $refundId,
                    'receipt_number' => $reference,
                    'session_id' => $sale->session_id,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customer_name,
                    'user_id' => $userId,
                    'cashier_id' => $userId,
                    'register_name' => $sale->register_name,
                    'terminal_id' => $sale->terminal_id,
                    'subtotal' => $this->refundTotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $this->refundTotal,
                    'payment_method' => $paymentMethodName,
                    'payment_method_id' => $paymentMethod?->id,
                    'amount_paid' => $this->refundTotal,
                    'change_given' => 0,
                    'balance_due' => 0,
                    'status' => 'refunded',
                    'settlement_status' => 'refunded',
                    'notes' => $this->refundReason ?: 'Refund',
                    'transaction_date' => now(),
                    'order_name' => 'Refund',
                    'document_type' => 'refund',
                    'transaction_type' => 'refund',
                    'original_transaction_id' => $sale->id,
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'is_deleted' => false,
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($selectedItems as $item) {
                    DB::table('transaction_items')->insert([
                        'id' => (string) Str::uuid(),
                        'transaction_id' => $refundId,
                        'product_id' => $item['product_id'],
                        'item_type' => 'product',
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'cost_price' => $item['cost_price'],
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'discount' => 0,
                        'discount_applied' => 0,
                        'line_total' => $item['line_total'],
                        'serial_id' => $item['serial_id'],
                        'serial_number' => $item['serial_number'],
                        'original_transaction_item_id' => $item['original_transaction_item_id'],
                        'sale_unit_id' => $item['sale_unit_id'],
                        'sale_unit_name' => $item['sale_unit_name'],
                        'base_quantity' => $item['base_quantity'],
                        'unit_conversion_to_base' => 1,
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'is_deleted' => false,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'sync_status' => 'pending',
                        'last_synced_at' => null,
                    ]);

                    if (!empty($item['serial_id'])) {
                        $newStatus = $item['condition'] === 'resalable' ? 'available' : 'damaged';

                        \App\Models\ProductSerial::query()
                            ->where('id', $item['serial_id'])
                            ->where('tenant_id', $tenantId)
                            ->update([
                                'status' => $newStatus,
                                'sold_at' => null,
                                'sale_id' => null,
                                'updated_at' => now(),
                            ]);
                    }

                    if ($item['product_id'] && $item['condition'] === 'resalable') {
                        StockMovement::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $branchId,
                            'product_id' => $item['product_id'],
                            'serial_id' => $item['serial_id'] ?? null,
                            'serial_number' => $item['serial_number'] ?? null,
                            'movement_type' => 'in',
                            'quantity' => $item['quantity'],
                            'sale_quantity' => $item['quantity'],
                            'base_quantity' => $item['quantity'],
                            'reference_id' => $refundId,
                            'reason' => 'Refund - resalable: ' . $reference,
                            'user_id' => $userId,
                            'movement_date' => now(),
                        ]);
                    }
                }

                DB::table('transaction_payments')->insert([
                    'id' => (string) Str::uuid(),
                    'transaction_id' => $refundId,
                    'session_id' => $sale->session_id,
                    'payment_method_id' => $paymentMethod?->id,
                    'payment_method' => $paymentMethodName,
                    'amount_paid' => $this->refundTotal,
                    'reference_number' => $reference,
                    'payment_date' => now(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'is_deleted' => false,
                    'sort_order' => 0,
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($sale->customer_id && (float) $sale->balance_due > 0) {
                    $ledgerAdjustment = -min($this->refundTotal, (float) $sale->balance_due);

                    $lastEntry = DB::table('customer_ledger')
                        ->where('customer_id', $sale->customer_id)
                        ->lockForUpdate()
                        ->orderByDesc('id')
                        ->first();

                    $balanceAfter = $lastEntry ? (float) $lastEntry->balance_after + $ledgerAdjustment : $ledgerAdjustment;

                    DB::table('customer_ledger')->insert([
                        'id' => (string) Str::uuid(),
                        'customer_id' => $sale->customer_id,
                        'transaction_id' => $refundId,
                        'type' => 'refund',
                        'description' => 'Refund ' . $reference . ' for ' . $sale->receipt_number,
                        'amount' => round($ledgerAdjustment, 2),
                        'balance_after' => round($balanceAfter, 2),
                        'date' => now(),
                        'created_at' => now(),
                        'sync_status' => 'pending',
                        'last_synced_at' => null,
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                    ]);

                    DB::table('customers')
                        ->where('id', $sale->customer_id)
                        ->update(['balance' => round($balanceAfter, 2), 'updated_at' => now()]);
                }
            });

            $this->showRefundModal = false;

            $this->receiptType = 'refund';
            $this->receiptData = [
                'reference' => $this->refundReceiptNumber,
                'date' => now()->format('d M Y, H:i'),
                'cashier' => auth()->user()?->name ?? 'Cashier',
                'customer' => $this->refundSaleData['customer_name'] ?? 'Walk-in',
                'original_receipt' => $this->refundSaleData['receipt_number'] ?? '',
                'payment_type' => $this->refundPaymentType,
                'items' => collect($this->refundItems)->where('selected', true)->values()->all(),
                'total' => $this->refundTotal,
                'branch' => app(\App\Services\BranchService::class)->getActiveBranch()?->name,
                'company' => $this->companyData(),
            ];
            $this->showReceiptModal = true;

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Refund processed',
                'description' => $this->currency() . ' ' . number_format($this->refundTotal, 0) . ' refunded.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Refund failed',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptData = null;
    }

    public function openSendEmailModal(string $docType, string $docId, string $toEmail = ''): void
    {
        $this->sendEmailDocType = $docType;
        $this->sendEmailDocId = $docId;
        $this->sendEmailTo = $toEmail;
        $this->sendEmailSubject = '';
        $this->sendEmailMessage = '';
        $this->sendEmailResult = '';
        $this->sendEmailSuccess = false;
        $this->showSendEmailModal = true;
    }

    public function closeSendEmailModal(): void
    {
        $this->showSendEmailModal = false;
    }

    public function sendEmailFromModal(): void
    {
        $this->sendEmailSending = true;
        $this->sendEmailResult = '';

        if (empty($this->sendEmailTo) || !filter_var($this->sendEmailTo, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmailResult = 'Please enter a valid email address.';
            $this->sendEmailSuccess = false;
            $this->sendEmailSending = false;
            return;
        }

        $service = new \App\Services\Email\PosEmailService();
        $tenantId = auth()->user()->tenant_id;

        $result = match ($this->sendEmailDocType) {
            'invoice' => $service->sendInvoice($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'receipt' => $service->sendReceipt($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'statement' => $service->sendStatement($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'payment_reminder' => $service->sendPaymentReminder($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            default => ['success' => false, 'message' => 'Unknown type.'],
        };

        $this->sendEmailSuccess = $result['success'];
        $this->sendEmailResult = $result['message'];
        $this->sendEmailSending = false;
    }

    protected function companyData(): array
    {
        $tenant = auth()->user()?->tenant;
        $business = $tenant?->business;

        return [
            'name' => $business?->name ?? $tenant?->name ?? config('app.name'),
            'address' => $business?->address ?? '',
            'phone' => $business?->phone ?? $tenant?->admin_phone ?? '',
            'tin' => $business?->tin ?? $tenant?->tin ?? '',
            'currency' => $this->currency(),
        ];
    }

    public function stats(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchService = app(\App\Services\BranchService::class);
        $canViewAll = (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
        $activeBranchId = $canViewAll ? null : $branchService->getActiveBranchId();

        $baseQuery = fn () => DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->where('document_type', '!=', 'credit_note')
            ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId));

        $todaySales = (float) $baseQuery()
            ->whereDate('transaction_date', today())
            ->where('status', '!=', 'refunded')
            ->sum('total_amount');

        $pendingQuery = $baseQuery()->where('balance_due', '>', 0);

        $activePlans = 0;
        if (Schema::hasTable('layaway_plans')) {
            $activePlans = DB::table('layaway_plans')
                ->where('tenant_id', $tenantId)
                ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
                ->count();
        }

        return [
            'todaySales' => $todaySales,
            'pendingAmount' => (float) $pendingQuery->sum('balance_due'),
            'pendingOrders' => (clone $pendingQuery)->count(),
            'activePlans' => $activePlans,
        ];
    }

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchService = app(\App\Services\BranchService::class);
        $canViewAll = (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
        $activeBranchId = $canViewAll ? null : $branchService->getActiveBranchId();

        return $table
            ->query(
                Transaction::query()
                    ->where('tenant_id', $tenantId)
                    ->where('is_deleted', false)
                    ->with(['cashier', 'branch'])
                    ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            )
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Receipt')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cashier.full_name')
                    ->label('Operated By')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $term = '%' . mb_strtolower($search) . '%';

                        return $query->whereExists(function ($subquery) use ($term) {
                            $subquery->selectRaw('1')
                                ->from('users')
                                ->whereRaw('CAST(transactions.cashier_id AS text) = CAST(users.id AS text)')
                                ->where(function ($userQuery) use ($term) {
                                    $userQuery->whereRaw('LOWER(users.full_name) LIKE ?', [$term])
                                        ->orWhereRaw('LOWER(users.email) LIKE ?', [$term]);
                                });
                        });
                    })
                    ->placeholder('—'),

                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ])
                    ->sortable(),

                TextColumn::make('register_name')
                    ->label('Register')
                    ->placeholder('—'),

                TextColumn::make('terminal_id')
                    ->label('Terminal')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('UGX')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('UGX')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => \App\Models\Branch::query()
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')),

                SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->options(fn () => \App\Models\User::query()
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')),

                SelectFilter::make('register_name')
                    ->label('Register')
                    ->options(fn () => Transaction::query()
                        ->where('tenant_id', $tenantId)
                        ->whereNotNull('register_name')
                        ->where('register_name', '!=', '')
                        ->distinct()
                        ->orderBy('register_name')
                        ->pluck('register_name', 'register_name')),

                SelectFilter::make('terminal_id')
                    ->label('Terminal')
                    ->options(fn () => Transaction::query()
                        ->where('tenant_id', $tenantId)
                        ->whereNotNull('terminal_id')
                        ->where('terminal_id', '!=', '')
                        ->distinct()
                        ->orderBy('terminal_id')
                        ->pluck('terminal_id', 'terminal_id')),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('viewDetails')
                        ->label('Show Details')
                        ->icon('heroicon-o-eye')
                        ->modalHeading(fn ($record) => 'Sales Detail — ' . ($record->receipt_number ?? 'N/A'))
                        ->modalWidth('7xl')
                        ->modalContent(function ($record) {
                            $items = \Illuminate\Support\Facades\DB::table('transaction_items')
                                ->where('transaction_id', $record->id)
                                ->where('is_deleted', false)
                                ->orderBy('sort_order')
                                ->orderBy('created_at')
                                ->get();

                            $payments = \Illuminate\Support\Facades\DB::table('transaction_payments')
                                ->where('transaction_id', $record->id)
                                ->where('is_deleted', false)
                                ->orderByDesc('payment_date')
                                ->get();

                            $customer = $record->customer_id
                                ? \App\Models\Customer::query()->find($record->customer_id)
                                : null;

                            $cashier = $record->cashier_id
                                ? \App\Models\User::query()->find($record->cashier_id)
                                : null;

                            $tenant = \App\Models\Tenant::query()->find(auth()->user()?->tenant_id);
                            $business = $tenant?->business;

                            $branchName = null;
                            if ($record->branch_id) {
                                $branch = \App\Models\Branch::query()->find($record->branch_id);
                                $branchName = $branch?->name;
                            }

                            return view('filament.tenant.pages.sales-detail', [
                                'sale' => $record,
                                'items' => $items,
                                'payments' => $payments,
                                'customer' => $customer,
                                'cashier' => $cashier,
                                'companyName' => $business?->name,
                                'companyAddress' => $business?->address,
                                'companyEmail' => $tenant?->admin_email ?? $business?->email,
                                'companyPhone' => $business?->phone ?? $tenant?->admin_phone,
                                'currencyCode' => $business?->currency_code ?? $tenant?->currency_code ?? 'UGX',
                                'branch_name' => $branchName,
                            ]);
                        })
                        ->modalSubmitAction(fn () => null)
                        ->modalCancelAction(fn () => null)
                        ->modalFooterActions(fn () => []),

                    Action::make('createPayment')
                        ->label('Create Payment')
                        ->icon('heroicon-o-plus')
                        ->visible(fn ($record) => in_array(strtolower((string) $record->status), ['pending', 'partial'], true))
                        ->form([
                            \Filament\Forms\Components\DatePicker::make('payment_date')
                                ->label('Date')
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('amount_paid')
                                ->label('Amount')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                            \Filament\Forms\Components\Select::make('payment_method')
                                ->label('Payment Type')
                                ->options([
                                    'cash' => 'Cash',
                                    'mobile_money' => 'Mobile Money',
                                    'card' => 'Card',
                                    'bank_transfer' => 'Bank Transfer',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $record) {
                            $amount = (float) $data['amount_paid'];
                            $due = max(0, (float) $record->total_amount - (float) $record->amount_paid);

                            if ($amount <= 0 || $amount > $due) {
                                $this->dispatch('toast', [
                                    'type' => 'warning',
                                    'title' => 'Invalid amount',
                                    'description' => 'Enter an amount between 1 and ' . number_format($due, 0) . '.',
                                ]);

                                return;
                            }

                            $methodName = $data['payment_method'];
                            $methodLabels = ['cash' => 'Cash', 'mobile_money' => 'Mobile Money', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer'];
                            $methodRecord = \App\Models\PaymentMethod::query()->where('name', $methodLabels[$methodName] ?? $methodName)->first();
                            $reference = 'PAY-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));

                            try {
                                \Illuminate\Support\Facades\DB::transaction(function () use ($record, $amount, $due, $methodRecord, $methodName, $methodLabels, $reference, $data) {
                                    $newPaid = (float) $record->amount_paid + $amount;
                                    $newDue = max(0, (float) $record->total_amount - $newPaid);
                                    $fullyPaid = $newDue <= 0;

                                    $record->update([
                                        'amount_paid' => $newPaid,
                                        'balance_due' => $newDue,
                                        'change_given' => 0,
                                        'status' => $fullyPaid ? 'completed' : 'partial',
                                        'settlement_status' => $fullyPaid ? 'paid' : 'partial',
                                    ]);

                                    $paymentPayload = [
                                        'id' => \Illuminate\Support\Str::uuid(),
                                        'transaction_id' => $record->id,
                                        'payment_method_id' => $methodRecord?->id,
                                        'payment_method' => $methodLabels[$methodName] ?? $methodName,
                                        'amount_paid' => $amount,
                                        'reference_number' => $reference,
                                        'payment_date' => $data['payment_date'],
                                        'session_id' => $record->session_id,
                                        'tenant_id' => $record->tenant_id,
                                        'is_deleted' => false,
                                        'sort_order' => 0,
                                        'sync_status' => 'pending',
                                        'last_synced_at' => null,
                                    ];

                                    if (\Illuminate\Support\Facades\Schema::hasColumn('transaction_payments', 'created_at')) {
                                        $paymentPayload['created_at'] = now();
                                    }

                                    if (\Illuminate\Support\Facades\Schema::hasColumn('transaction_payments', 'updated_at')) {
                                        $paymentPayload['updated_at'] = now();
                                    }

                                    \Illuminate\Support\Facades\DB::table('transaction_payments')->insert($paymentPayload);

                                    if ($record->customer_id) {
                                        $isLayaway = \Illuminate\Support\Facades\DB::table('layaway_plans')->where('transaction_id', $record->id)->exists();

                                        $type = $isLayaway ? 'layaway_payment' : 'credit_payment';

                                        $lastEntry = \Illuminate\Support\Facades\DB::table('customer_ledger')
                                            ->where('customer_id', $record->customer_id)
                                            ->lockForUpdate()
                                            ->orderByDesc('id')
                                            ->first();

                                        $balanceAfter = $lastEntry ? (float) $lastEntry->balance_after - $amount : -$amount;

                                        \Illuminate\Support\Facades\DB::table('customer_ledger')->insert([
                                            'id' => (string) \Illuminate\Support\Str::uuid(),
                                            'customer_id' => $record->customer_id,
                                            'transaction_id' => $record->id,
                                            'type' => $type,
                                            'description' => 'Payment ' . $reference . ' for ' . $record->receipt_number,
                                            'amount' => round(-$amount, 2),
                                            'balance_after' => round($balanceAfter, 2),
                                            'date' => now(),
                                            'created_at' => now(),
                                            'sync_status' => 'pending',
                                            'last_synced_at' => null,
                                            'tenant_id' => $record->tenant_id,
                                            'branch_id' => $record->branch_id,
                                        ]);

                                        \Illuminate\Support\Facades\DB::table('customers')
                                            ->where('id', $record->customer_id)
                                            ->update(['balance' => round($balanceAfter, 2), 'updated_at' => now()]);

                                        if ($isLayaway) {
                                            \Illuminate\Support\Facades\DB::table('layaway_plans')
                                                ->where('transaction_id', $record->id)
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

                            $this->dispatch('toast', [
                                'type' => 'success',
                                'title' => 'Payment recorded',
                                'description' => $reference . ' — ' . number_format($amount, 0) . ' received.',
                            ]);

                            $items = DB::table('transaction_items')
                                ->where('transaction_id', $record->id)
                                ->where('is_deleted', false)
                                ->get()
                                ->map(fn ($item) => [
                                    'name' => $item->product_name,
                                    'quantity' => (float) $item->quantity,
                                    'unit_price' => (float) $item->unit_price,
                                    'line_total' => (float) $item->line_total,
                                ])
                                ->toArray();

                            $this->receiptType = 'payment';
                            $this->receiptData = [
                                'reference' => $reference,
                                'date' => now()->format('d M Y, H:i'),
                                'cashier' => auth()->user()?->name ?? 'Cashier',
                                'customer' => $record->customer_name ?? 'Walk-in',
                                'invoice' => $record->receipt_number,
                                'invoice_date' => \Carbon\Carbon::parse($record->transaction_date)->format('d M Y'),
                                'payment_method' => $methodLabels[$methodName] ?? $methodName,
                                'items' => $items,
                                'total' => (float) $record->total_amount,
                                'paid_total' => $newPaid,
                                'amount' => $amount,
                                'remaining' => $newDue,
                                'branch' => app(\App\Services\BranchService::class)->getActiveBranch()?->name,
                                'company' => $this->companyData(),
                            ];
                            $this->showReceiptModal = true;
                        }),

                    Action::make('showPayments')
                        ->label('Show Payments')
                        ->icon('heroicon-o-banknotes')
                        ->modalHeading(fn ($record) => 'Payments for ' . ($record->receipt_number ?? 'Sale'))
                        ->modalWidth('4xl')
                        ->modalContent(fn ($record) => view('filament.tenant.pages.sales-payments', ['payments' => \Illuminate\Support\Facades\DB::table('transaction_payments')->where('transaction_id', $record->id)->orderByDesc('payment_date')->get()])),

                    Action::make('refundSale')
                        ->label('Refund Sale')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->visible(fn ($record) => (bool) (auth()->user()?->hasPermission('can_process_refunds') ?? false)
                            && in_array(strtolower((string) $record->status), ['completed', 'partial'], true)
                            && strtolower((string) $record->document_type) !== 'refund')
                        ->action(fn ($record) => $this->openRefundModal($record->id)),

                    Action::make('deleteSale')
                        ->label('Delete Sale')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => $this->canDeleteSales())
                        ->action(function ($record) {
                            $record->update(['is_deleted' => true]);

                            $this->dispatch('toast', ['type' => 'error', 'title' => 'Sale deleted']);
                        }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->searchPlaceholder('Search sales by receipt or customer');
    }
}
