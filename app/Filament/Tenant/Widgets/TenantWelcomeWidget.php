<?php

namespace App\Filament\Tenant\Widgets;

use App\Services\BranchService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantWelcomeWidget extends Widget
{
    protected string $view = 'filament.tenant.widgets.tenant-welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $user = Auth::user();
        $userName = $user->full_name ?? 'Admin';
        $tenantId = $user?->tenant_id;
        $cashier = $user?->scopedCashierId();
        $branchId = app(BranchService::class)->getActiveBranchId();

        $todayOrders = 0;
        try {
            $todayOrders = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->whereDate('transaction_date', now()->toDateString())
                ->count();
        } catch (\Exception $e) {}

        $lowStockProduct = null;
        try {
            if ($branchId) {
                $lowStockProduct = DB::table('branch_stocks as bs')
                    ->join('products as p', 'bs.product_id', '=', 'p.id')
                    ->where('bs.branch_id', $branchId)
                    ->where('p.tenant_id', $tenantId)
                    ->where('p.is_deleted', false)
                    ->whereColumn('bs.quantity', '<=', 'p.reorder_level')
                    ->orderBy('bs.quantity')
                    ->select('p.name', 'bs.quantity as current_stock', 'p.reorder_level')
                    ->first();
            } else {
                $lowStockProduct = DB::table('products')
                    ->where('tenant_id', $tenantId)
                    ->where('is_deleted', false)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->orderBy('current_stock')
                    ->select('name', 'current_stock', 'reorder_level')
                    ->first();
            }
        } catch (\Exception $e) {}

        return [
            'firstName' => explode(' ', $userName)[0],
            'userName' => $userName,
            'todayOrders' => $todayOrders,
            'lowStockProduct' => $lowStockProduct,
            'dateRange' => now()->startOfWeek()->format('d/m/Y') . ' - ' . now()->format('d/m/Y'),
        ];
    }
}