<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\CustomerWallet;
use App\Services\CustomerWalletService;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class WalletPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $title = 'Customer Wallets';
    protected static ?string $slug = 'wallets';
    protected static ?int $navigationSort = 26;
    protected string $view = 'filament.tenant.pages.wallet-page';

    public static function getNavigationLabel(): string
    {
        return __('navigation.wallets') ?? 'Wallets';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.customers') ?? 'Customers';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        return $table
            ->query(
                CustomerWallet::query()
                    ->where('tenant_id', $tenantId)
                    ->with('customer')
                    ->whereHas('customer', fn ($q) => $q->where('is_deleted', false))
            )
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('total_deposited')
                    ->label('Deposited')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('total_used')
                    ->label('Used')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                \Filament\Actions\Action::make('deposit')
                    ->label('Deposit')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix('UGX'),
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Optional reason'),
                    ])
                    ->action(function (CustomerWallet $record, array $data): void {
                        app(CustomerWalletService::class)->deposit(
                            $record->tenant_id,
                            $record->customer_id,
                            (float) $data['amount'],
                            app(\App\Services\BranchService::class)->getActiveBranchId(),
                            'manual',
                            null,
                            $data['description'] ?: 'Manual deposit by ' . auth()->user()->full_name
                        );

                        $this$this->dispatch('gooey-toast', [
                            'type' => 'success',
                            'title' => 'Deposit successful',
                            'description' => number_format($data['amount'], 2) . ' UGX deposited for {$record->customer->full_name}',
                        ]);
                    }),

                \Filament\Actions\Action::make('withdraw')
                    ->label('Withdraw')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount to Withdraw')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix('UGX'),
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Optional reason'),
                    ])
                    ->action(function (CustomerWallet $record, array $data): void {
                        try {
                            app(CustomerWalletService::class)->useFunds(
                                $record->tenant_id,
                                $record->customer_id,
                                (float) $data['amount'],
                                app(\App\Services\BranchService::class)->getActiveBranchId(),
                                'manual',
                                null,
                                $data['description'] ?: 'Manual withdrawal by ' . auth()->user()->full_name
                            );

                            $this$this->dispatch('gooey-toast', [
                                'type' => 'success',
                                'title' => 'Withdrawal successful',
                                'description' => number_format($data['amount'], 2) . ' UGX withdrawn for {$record->customer->full_name}',
                            ]);
                        } catch (\RuntimeException $e) {
                            $this$this->dispatch('gooey-toast', [
                                'type' => 'error',
                                'title' => 'Withdrawal failed',
                                'description' => $e->getMessage(),
                            ]);
                        }
                    }),

                \Filament\Actions\Action::make('history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Wallet History')
                    ->modalSubmitActionLabel('Close')
                    ->modalContent(function (CustomerWallet $record) {
                        $transactions = \App\Models\WalletTransaction::where('wallet_id', $record->id)
                            ->orderByDesc('created_at')
                            ->limit(20)
                            ->get();

                        $html = '<div style="space-y:2">';
                        $html .= '<p><strong>Customer:</strong> ' . e($record->customer->full_name) . ' | <strong>Balance:</strong> UGX ' . number_format($record->balance, 2) . '</p>';
                        $html .= '<hr style="margin:8px 0;border-color:#e5e7eb;">';
                        foreach ($transactions as $tx) {
                            $color = $tx->amount >= 0 ? '#16a34a' : '#dc2626';
                            $sign = $tx->amount >= 0 ? '+' : '';
                            $html .= '<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;">';
                            $html .= '<span style="font-size:13px;">' . e($tx->description ?? $tx->type) . '</span>';
                            $html .= '<span style="font-size:13px;color:' . $color . ';font-weight:600;">' . $sign . number_format(abs($tx->amount), 2) . '</span>';
                            $html .= '</div>';
                        }
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->action(fn () => null),
            ])
            ->bulkActions([]);
    }
}
