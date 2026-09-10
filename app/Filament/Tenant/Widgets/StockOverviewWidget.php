<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Product;
use App\Services\BranchService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $branchId = app(BranchService::class)->getActiveBranchId();

        if ($branchId) {
            $totalInventory = DB::table('branch_stocks')->where('branch_id', $branchId)->sum('quantity');
            $stockValue = DB::table('branch_stocks as bs')
                ->join('products as p', 'bs.product_id', '=', 'p.id')
                ->where('bs.branch_id', $branchId)
                ->sum(DB::raw('bs.quantity * p.billing_price'));
            $lowStockItems = DB::table('branch_stocks as bs')
                ->join('products as p', 'bs.product_id', '=', 'p.id')
                ->where('bs.branch_id', $branchId)
                ->whereColumn('bs.quantity', '<=', 'p.reorder_level')
                ->count();
            $recentAdditions = DB::table('branch_stocks')->where('branch_id', $branchId)->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        } else {
            $totalInventory = Product::sum('current_stock');
            $lowStockItems = Product::whereColumn('current_stock', '<=', 'reorder_level')->count();
            $stockValue = Product::sum(DB::raw('current_stock * billing_price'));
            $recentAdditions = Product::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        }

        return [
            Stat::make('Total Inventory', number_format($totalInventory, 0))
                ->icon('heroicon-o-archive-box'),
            Stat::make('Low Stock Items', number_format($lowStockItems, 0))
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStockItems > 0 ? 'danger' : 'success'),
            Stat::make('Stock Value', 'UGX ' . number_format($stockValue, 0))
                ->icon('heroicon-o-banknotes'),
            Stat::make('Recent Additions', number_format($recentAdditions, 0))
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
