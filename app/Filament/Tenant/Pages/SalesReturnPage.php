<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Transaction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class SalesReturnPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    public ?string $selectedProductId = null;

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

        $this->selectedProductId = $productId;
        $this$this->dispatch('gooey-toast', ['type' => 'info', 'title' => 'Product selected for return', 'description' => $product->name]);
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?int $navigationSort = 2;

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
        return __('navigation.sales_return');
    }


    protected static ?string $title = 'Sales Return';

    protected string $view = 'filament.tenant.pages.sales-return-page';

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
                    ->with('branch')
                    ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
                    ->where(function ($query) {
                        $query->whereIn('document_type', ['refund', 'return', 'sale_return', 'sales_return'])
                            ->orWhereIn('status', ['refunded', 'returned', 'partial_refund'])
                            ->orWhere('total_amount', '<', 0);
                    })
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

                TextColumn::make('transaction_date')
                    ->label('Returned Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('document_type')
                    ->label('Type')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state ?: 'Return')),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => ['refunded', 'returned'],
                        'warning' => 'partial_refund',
                        'info' => 'processing',
                    ])
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment')
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
            ->defaultSort('transaction_date', 'desc')
            ->searchPlaceholder('Search returns or refunds');
    }
}
