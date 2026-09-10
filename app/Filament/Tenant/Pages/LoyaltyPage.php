<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Services\CustomerLoyaltyService;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForm;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class LoyaltyPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $title = 'Loyalty Program';
    protected static ?string $slug = 'loyalty';
    protected static ?int $navigationSort = 25;
    protected string $view = 'filament.tenant.pages.loyalty-page';

    public bool $showEarnModal = false;
    public bool $showRedeemModal = false;
    public ?string $selectedAccountId = null;
    public ?string $selectedCustomerName = null;
    public string $earnPoints = '';
    public string $redeemPoints = '';
    public string $earnDescription = '';
    public string $redeemDescription = '';

    public static function getNavigationLabel(): string
    {
        return __('navigation.loyalty') ?? 'Loyalty';
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
        $branchService = app(\App\Services\BranchService::class);
        $canViewAll = (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
        $activeBranchId = $canViewAll ? null : $branchService->getActiveBranchId();

        return $table
            ->query(
                LoyaltyAccount::query()
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
                    ->label('Points')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total_earned')
                    ->label('Earned')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total_redeemed')
                    ->label('Redeemed')
                    ->sortable()
                    ->alignCenter(),

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
                \Filament\Actions\Action::make('earn')
                    ->label('Earn')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('points')
                            ->label('Points to Earn')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Optional reason'),
                    ])
                    ->action(function (LoyaltyAccount $record, array $data): void {
                        app(CustomerLoyaltyService::class)->earnPoints(
                            $record->tenant_id,
                            $record->customer_id,
                            (int) $data['points'],
                            app(\App\Services\BranchService::class)->getActiveBranchId(),
                            'manual',
                            null,
                            $data['description'] ?: 'Manual earn by ' . auth()->user()->full_name
                        );

                        $this->dispatch('toast', [
                            'type' => 'success',
                            'title' => 'Points earned',
                            'description' => "{$data['points']} points added to {$record->customer->full_name}",
                        ]);
                    }),

                \Filament\Actions\Action::make('redeem')
                    ->label('Redeem')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('points')
                            ->label('Points to Redeem')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Optional reason'),
                    ])
                    ->action(function (LoyaltyAccount $record, array $data): void {
                        try {
                            app(CustomerLoyaltyService::class)->redeemPoints(
                                $record->tenant_id,
                                $record->customer_id,
                                (int) $data['points'],
                                app(\App\Services\BranchService::class)->getActiveBranchId(),
                                'manual',
                                null,
                                $data['description'] ?: 'Manual redemption by ' . auth()->user()->full_name
                            );

                            $this->dispatch('toast', [
                                'type' => 'success',
                                'title' => 'Points redeemed',
                                'description' => "{$data['points']} points redeemed for {$record->customer->full_name}",
                            ]);
                        } catch (\RuntimeException $e) {
                            $this->dispatch('toast', [
                                'type' => 'error',
                                'title' => 'Redemption failed',
                                'description' => $e->getMessage(),
                            ]);
                        }
                    }),

                \Filament\Actions\Action::make('history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Loyalty History')
                    ->modalSubmitActionLabel('Close')
                    ->modalContent(function (LoyaltyAccount $record) {
                        $transactions = \App\Models\LoyaltyTransaction::where('loyalty_account_id', $record->id)
                            ->orderByDesc('created_at')
                            ->limit(20)
                            ->get();

                        $html = '<div style="space-y-2">';
                        $html .= '<p><strong>Customer:</strong> ' . e($record->customer->full_name) . ' | <strong>Balance:</strong> ' . number_format($record->balance) . ' pts</p>';
                        $html .= '<hr style="margin:8px 0;border-color:#e5e7eb;">';
                        foreach ($transactions as $tx) {
                            $color = $tx->points >= 0 ? '#16a34a' : '#dc2626';
                            $sign = $tx->points >= 0 ? '+' : '';
                            $html .= '<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;">';
                            $html .= '<span style="font-size:13px;">' . e($tx->description ?? $tx->type) . '</span>';
                            $html .= '<span style="font-size:13px;color:' . $color . ';font-weight:600;">' . $sign . $tx->points . ' pts</span>';
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
