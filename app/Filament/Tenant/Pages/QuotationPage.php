<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\QuotationSaleConverter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuotationPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    // Send email modal state
    public bool $showSendEmailModal = false;
    public string $sendEmailDocType = 'quotation';
    public string $sendEmailDocId = '';
    public string $sendEmailTo = '';
    public string $sendEmailSubject = '';
    public string $sendEmailMessage = '';
    public bool $sendEmailSending = false;
    public string $sendEmailResult = '';
    public bool $sendEmailSuccess = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.sales');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.quotations');
    }


    protected static ?string $title = 'Quotation';

    protected string $view = 'filament.tenant.pages.quotation-page';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('createQuotation')
                ->label('Create Quotation')
                ->modalWidth('7xl')
                ->model(Quotation::class)
                ->mutateFormDataUsing(function (array $data): array {
                    $items = $data['items'] ?? [];
                    $subtotal = collect($items)->sum(fn ($item) => ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)));
                    $discount = (float) ($data['discount_amount'] ?? 0);
                    $tax = (float) ($data['tax_amount'] ?? 0);

                    unset($data['items']);

                    $data['quote_number'] = $data['quote_number'] ?? 'QT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                    $data['tenant_id'] = auth()->user()?->tenant_id;
                    $data['user_id'] = auth()->id();
                    $data['status'] = $data['status'] ?? 'draft';
                    $data['subtotal'] = round($subtotal, 2);
                    $data['tax_amount'] = round($tax, 2);
                    $data['discount_amount'] = round($discount, 2);
                    $data['total_amount'] = round(max($subtotal + $tax - $discount, 0), 2);
                    $data['branch_id'] = app(\App\Services\BranchService::class)->getActiveBranchId();

                    return $data;
                })
                ->form($this->getQuotationForm())
                ->after(function ($record, array $data) {
                    $this->createQuotationItems($record, $data['items'] ?? []);
                }),
        ];
    }

    private function getQuotationForm(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return [
            Select::make('customer_id')
                ->label('Customer Name')
                ->options(Customer::query()->where('tenant_id', $tenantId)->where('is_deleted', false)->pluck('full_name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set) {
                    $customer = Customer::find($state);
                    if ($customer) {
                        $set('customer_name', $customer->full_name);
                    }
                })
                ->required()
                ->columnSpan(1),
            DatePicker::make('valid_until')
                ->label('Date')
                ->default(now())
                ->required()
                ->columnSpan(1),
            TextInput::make('quote_number')
                ->label('Reference Number')
                ->disabled()
                ->default(fn () => 'QT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)))
                ->columnSpan(1),

            Repeater::make('items')
                ->label('Products')
                ->live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $this->calculateTotals($state, $set, $get);
                })
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::query()->where('tenant_id', $tenantId)->where('is_deleted', false)->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set) use ($tenantId) {
                            $product = Product::where('tenant_id', $tenantId)->where('id', $state)->first();
                            if ($product) {
                                $set('product_name', $product->name);
                                $set('unit_price', (float) $product->billing_price);
                                $set('tax_rate', (float) $product->tax_rate ?? 0);
                                $set('cost_price', (float) $product->cost_price ?? 0);
                                $set('line_total', ((float) ($set('quantity') ?? 0)) * ((float) $product->billing_price));
                            }
                        }),
                    TextInput::make('quantity')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->live(onBlur: true),
                    TextInput::make('unit_price')
                        ->label('Purchase Price($)')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->live(onBlur: true),
                    TextInput::make('item_discount')
                        ->label('Discount($)')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true),
                    TextInput::make('item_tax_amount')
                        ->label('Tax Amount($)')
                        ->numeric()
                        ->default(0)
                        ->readOnly(),
                    TextInput::make('line_total')
                        ->label('Total Cost(%)')
                        ->numeric()
                        ->default(0)
                        ->readOnly(),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->reorderable(false)
                ->maxItems(50)
                ->columnSpanFull(),

            TextInput::make('order_tax')
                ->label('Order Tax')
                ->numeric()
                ->default(0)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set, $get) {
                    $this->calculateTotals($get('items') ?? [], $set, $get);
                })
                ->columnSpan(1),
            TextInput::make('discount_amount')
                ->label('Discount')
                ->numeric()
                ->default(0)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set, $get) {
                    $this->calculateTotals($get('items') ?? [], $set, $get);
                })
                ->columnSpan(1),
            TextInput::make('shipping')
                ->label('Shipping')
                ->numeric()
                ->default(0)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set, $get) {
                    $this->calculateTotals($get('items') ?? [], $set, $get);
                })
                ->columnSpan(1),

            RichEditor::make('notes')
                ->label('Description')
                ->columnSpanFull(),
            Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'approved' => 'Approved',
                    'expired' => 'Expired',
                ])
                ->default('draft')
                ->required()
                ->columnSpan(1),
            
            TextInput::make('tax_amount_summary')
                ->label('Order Tax')
                ->numeric()
                ->default(0)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('discount_summary')
                ->label('Discount')
                ->numeric()
                ->default(0)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('shipping_summary')
                ->label('Shipping')
                ->numeric()
                ->default(0)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('total_amount')
                ->label('Grand Total')
                ->numeric()
                ->default(0)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->extraAttributes(['class' => 'text-lg font-bold'])
                ->columnSpan(1),
        ];
    }

    private function calculateTotals($items, $set, $get): void
    {
        $subtotal = collect($items ?? [])->sum(fn ($item) => ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)));
        $discount = (float) ($get('discount_amount') ?? 0);
        $tax = (float) ($get('order_tax') ?? 0);
        $shipping = (float) ($get('shipping') ?? 0);

        $set('subtotal', round($subtotal, 2));
        $set('tax_amount_summary', round($tax, 2));
        $set('discount_summary', round($discount, 2));
        $set('shipping_summary', round($shipping, 2));
        $set('total_amount', round($subtotal + $tax - $discount + $shipping, 2));
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
                Quotation::query()
                    ->where('tenant_id', $tenantId)
                    ->where('is_deleted', false)
                    ->with('branch')
                    ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            )
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Quote No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'draft',
                        'info' => 'sent',
                        'danger' => 'expired',
                    ])
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                ...($canViewAll ? [
                    \Filament\Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                        ->placeholder('All Branches'),
                ] : []),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('viewQuotation')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->modalHeading(fn ($record) => 'Quotation Details - ' . $record->quote_number)
                        ->modalWidth('7xl')
                        ->modalSubmitActionLabel('Save Changes')
                        ->form(function ($record) {
                            return $this->getQuotationDetailForm($record);
                        })
                        ->fillForm(function ($record) {
                            $items = \Illuminate\Support\Facades\DB::table('quotation_items')
                                ->where('quotation_id', $record->id)
                                ->get()
                                ->map(fn ($item) => [
                                    'product_id' => $item->product_id,
                                    'product_name' => $item->product_name,
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'item_tax_amount' => $item->tax_amount,
                                    'item_discount' => $item->discount_amount,
                                    'line_total' => $item->line_total,
                                ])
                                ->toArray();

                            return [
                                'customer_name' => $record->customer_name,
                                'valid_until' => $record->valid_until,
                                'quote_number' => $record->quote_number,
                                'items' => $items,
                                'tax_amount' => $record->tax_amount,
                                'discount_amount' => $record->discount_amount,
                                'shipping_amount' => 0,
                                'notes' => $record->notes,
                                'status' => $record->status,
                                'tax_amount_display' => $record->tax_amount,
                                'discount_amount_display' => $record->discount_amount,
                                'shipping_amount_display' => 0,
                                'total_amount_display' => $record->total_amount,
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $this->updateQuotation($record, $data);

                            $this->dispatch('toast', [
                                'type' => 'success',
                                'title' => 'Quotation updated successfully',
                            ]);
                        }),

                    Action::make('convertToSale')
                        ->label('Convert to Sale')
                        ->icon('heroicon-o-shopping-cart')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => 'Convert Quotation to Sale')
                        ->modalDescription('This creates a completed POS sale from this quotation and opens the POS screen.')
                        ->action(function ($record) {
                            $converter = app(QuotationSaleConverter::class);
                            $sale = $converter->convert($record);

                            $this->dispatch('toast', [
                                'type' => 'success',
                                'title' => 'Quotation converted to sale',
                            ]);

                            return redirect()->to('/tenant/sales-terminal?sale_id=' . $sale->id);
                        }),

                    Action::make('sendEmail')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->action(fn ($record) => $this->openSendEmailModal('quotation', $record->id)),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search quotation by number or customer');
    }

    private function getQuotationDetailForm($record): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return [
            TextInput::make('customer_name')
                ->label('Customer Name')
                ->default($record->customer_name)
                ->disabled()
                ->columnSpan(1),
            DatePicker::make('valid_until')
                ->label('Date')
                ->default($record->valid_until)
                ->required()
                ->columnSpan(1),
            TextInput::make('quote_number')
                ->label('Reference Number')
                ->default($record->quote_number)
                ->disabled()
                ->columnSpan(1),

            Repeater::make('items')
                ->label('Products')
                ->live()
                ->schema([
                    TextInput::make('product_name')
                        ->label('Product')
                        ->disabled()
                        ->required(),
                    TextInput::make('quantity')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->disabled(),
                    TextInput::make('unit_price')
                        ->label('Purchase Price($)')
                        ->numeric()
                        ->default(0)
                        ->prefix('$ ')
                        ->disabled(),
                    TextInput::make('item_discount')
                        ->label('Discount($)')
                        ->numeric()
                        ->default(0)
                        ->prefix('$ ')
                        ->disabled(),
                    TextInput::make('item_tax_amount')
                        ->label('Tax Amount($)')
                        ->numeric()
                        ->default(0)
                        ->prefix('$ ')
                        ->readOnly()
                        ->disabled(),
                    TextInput::make('line_total')
                        ->label('Total Cost(%)')
                        ->numeric()
                        ->default(0)
                        ->prefix('$ ')
                        ->readOnly()
                        ->disabled(),
                ])
                ->maxItems(50)
                ->columnSpanFull(),

            TextInput::make('tax_amount')
                ->label('Order Tax')
                ->numeric()
                ->default($record->tax_amount)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->columnSpan(1),
            TextInput::make('discount_amount')
                ->label('Discount')
                ->numeric()
                ->default($record->discount_amount)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->columnSpan(1),
            TextInput::make('shipping_amount')
                ->label('Shipping')
                ->numeric()
                ->default(0)
                ->prefix('$ ')
                ->live(onBlur: true)
                ->columnSpan(1),

            RichEditor::make('notes')
                ->label('Description')
                ->default($record->notes)
                ->columnSpanFull(),
            Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'approved' => 'Approved',
                    'expired' => 'Expired',
                ])
                ->default($record->status)
                ->required()
                ->columnSpan(1),

            TextInput::make('tax_amount_display')
                ->label('Order Tax (Summary)')
                ->numeric()
                ->default($record->tax_amount)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('discount_amount_display')
                ->label('Discount (Summary)')
                ->numeric()
                ->default($record->discount_amount)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('shipping_amount_display')
                ->label('Shipping (Summary)')
                ->numeric()
                ->default(0)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->columnSpan(1),
            TextInput::make('total_amount_display')
                ->label('Grand Total')
                ->numeric()
                ->default($record->total_amount)
                ->readOnly()
                ->prefix('$ ')
                ->disabled()
                ->extraAttributes(['class' => 'text-lg font-bold'])
                ->columnSpan(1),
        ];
    }

    private function updateQuotation($record, array $data): void
    {
        $record->update([
            'customer_id' => $data['customer_id'] ?? $record->customer_id,
            'valid_until' => $data['valid_until'] ?? $record->valid_until,
            'tax_amount' => $data['tax_amount'] ?? $record->tax_amount,
            'discount_amount' => $data['discount_amount'] ?? $record->discount_amount,
            'status' => $data['status'] ?? $record->status,
            'notes' => $data['notes'] ?? $record->notes,
        ]);
    }

    private function createQuotationItems($record, $items): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();

        if (! empty($items)) {
            foreach ($items as $item) {
                \Illuminate\Support\Facades\DB::table('quotation_items')->insert([
                    'id' => (string) Str::uuid(),
                    'quotation_id' => $record->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'] ?? 'Product',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => ((float) ($item['quantity'] ?? 1)) * ((float) ($item['unit_price'] ?? 0)),
                    'created_at' => now(),
                    'sync_status' => 'pending',
                    'last_synced_at' => null,
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                ]);
            }
        }
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
            'quotation' => $service->sendQuotation($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'statement' => $service->sendStatement($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            'payment_reminder' => $service->sendPaymentReminder($this->sendEmailDocId, $this->sendEmailTo, $this->sendEmailSubject ?: null, $this->sendEmailMessage ?: null, $tenantId),
            default => ['success' => false, 'message' => 'Unknown type.'],
        };

        $this->sendEmailSuccess = $result['success'];
        $this->sendEmailResult = $result['message'];
        $this->sendEmailSending = false;
    }
}

