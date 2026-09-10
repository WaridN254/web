<?php

namespace App\Filament\Tenant\Widgets;

use App\Services\BranchService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantStatsWidget extends Widget
{
    protected string $view = 'filament.tenant.widgets.tenant-stats-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getViewData(): array
    {
        $user = auth()->user();
        $tenantId = $user?->tenant_id;
        $cashier = $user?->scopedCashierId();
        $currency = $user?->tenant?->currency ?? 'UGX';
        $branchId = app(BranchService::class)->getActiveBranchId();

        $cacheKey = "tenant:{$tenantId}:widget_stats";

        return Cache::remember($cacheKey, 300, function () use ($tenantId, $cashier, $currency, $branchId) {
            return $this->computeData($tenantId, $cashier, $currency, $branchId);
        });
    }

    protected function computeData($tenantId, $cashier, $currency, $branchId): array
    {
        $grossRevenue = 0;
        $totalRefunds = 0;
        $totalDiscounts = 0;
        $totalPurchase = 0;
        $totalPurchaseReturn = 0;
        $totalCostOfGoods = 0;
        $invoiceDue = 0;
        $actualExpenses = 0;
        $netRevenue = 0;
        $paymentReturns = 0;

        try {
            $salesData = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN total_amount + COALESCE(discount_amount, 0) ELSE 0 END), 0) as gross_revenue,
                    COALESCE(SUM(CASE WHEN document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN total_amount ELSE 0 END), 0) as net_revenue,
                    COALESCE(SUM(CASE WHEN document_type = 'refund' OR status = 'refunded' THEN total_amount ELSE 0 END), 0) as total_refunds,
                    COALESCE(SUM(CASE WHEN document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN discount_amount ELSE 0 END), 0) as total_discounts,
                    COALESCE(SUM(CASE WHEN document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' AND settlement_status IN ('partial','unpaid') THEN balance_due ELSE 0 END), 0) as invoice_due
                ")
                ->first();

            if ($salesData) {
                $grossRevenue = $salesData->gross_revenue;
                $netRevenue = $salesData->net_revenue;
                $totalRefunds = $salesData->total_refunds;
                $totalDiscounts = $salesData->total_discounts;
                $invoiceDue = $salesData->invoice_due;
            }
        } catch (\Exception $e) {}

        try {
            $actualExpenses = (float) DB::table('cash_movements')
                ->where('tenant_id', $tenantId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where(function ($query) {
                    $query->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                        ->orWhere('category', 'expense');
                })
                ->sum('amount');
        } catch (\Exception $e) {}

        try {
            $purchaseData = DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->selectRaw("COALESCE(SUM(CASE WHEN status = 'returned' THEN grand_total ELSE 0 END), 0) as total_purchase_return")
                ->first();

            if ($purchaseData) {
                $totalPurchaseReturn = $purchaseData->total_purchase_return;
            }
        } catch (\Exception $e) {}

        try {
            $totalCostOfGoods = (float) DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->where('ti.tenant_id', $tenantId)
                ->where('t.tenant_id', $tenantId)
                ->where('t.is_deleted', false)
                ->where('t.status', 'completed')
                ->whereNotIn('t.document_type', ['credit_note', 'refund', 'return'])
                ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->sum(DB::raw('COALESCE(ti.cost_price, 0) * COALESCE(ti.quantity, 0)'));
        } catch (\Exception $e) {}

        $profit = $netRevenue - $totalCostOfGoods - $actualExpenses;
        $paymentReturns = $totalRefunds + $totalPurchaseReturn;

        $m = $this->monthOverMonth($tenantId, $cashier, $branchId);
        $pct = function ($cur, $prev) {
            if (!$prev || $prev == 0) {
                return null;
            }
            return round((($cur - $prev) / $prev) * 100, 1);
        };

        return [
            'currency' => $currency,
            'grossRevenue' => $grossRevenue,
            'netRevenue' => $netRevenue,
            'profit' => $profit,
            'totalRefunds' => $totalRefunds,
            'totalPurchase' => $totalPurchase,
            'totalPurchaseReturn' => $totalPurchaseReturn,
            'actualExpenses' => $actualExpenses,
            'invoiceDue' => $invoiceDue,
            'paymentReturns' => $paymentReturns,
            'deltaGrossRevenue' => $pct($m['grossCur'], $m['grossPrev']),
            'deltaNetRevenue' => $pct($m['netCur'], $m['netPrev']),
            'deltaProfit' => $pct($m['profitCur'], $m['profitPrev']),
            'deltaRefunds' => $pct($m['refCur'], $m['refPrev']),
            'deltaPurchase' => $pct($m['purchaseCur'], $m['purchasePrev']),
            'deltaPurchaseReturn' => $pct($m['purchaseRetCur'], $m['purchaseRetPrev']),
            'deltaExpenses' => $pct($m['expCur'], $m['expPrev']),
            'deltaInvoiceDue' => $pct($m['dueCur'], $m['duePrev']),
            'deltaPaymentReturns' => $pct($m['payRetCur'], $m['payRetPrev']),
        ];
    }

    protected function monthOverMonth($tenantId, $cashier = null, $branchId = null): array
    {
        $sales = ['grossCur' => 0, 'grossPrev' => 0, 'refCur' => 0, 'refPrev' => 0, 'dueCur' => 0, 'duePrev' => 0, 'discCur' => 0, 'discPrev' => 0];
        $purchase = ['purchaseCur' => 0, 'purchasePrev' => 0, 'purchaseRetCur' => 0, 'purchaseRetPrev' => 0];

        try {
            $sales = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('cashier_id', $cashier))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) AND document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN total_amount + COALESCE(discount_amount, 0) END), 0) as gross_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) - interval '1 month' AND document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN total_amount + COALESCE(discount_amount, 0) END), 0) as gross_prev,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) AND (document_type = 'refund' OR status = 'refunded') THEN total_amount END), 0) as ref_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) - interval '1 month' AND (document_type = 'refund' OR status = 'refunded') THEN total_amount END), 0) as ref_prev,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) AND settlement_status IN ('partial','unpaid') THEN balance_due END), 0) as due_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) - interval '1 month' AND settlement_status IN ('partial','unpaid') THEN balance_due END), 0) as due_prev,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) AND document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN discount_amount END), 0) as disc_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', transaction_date) = date_trunc('month', now()) - interval '1 month' AND document_type NOT IN ('credit_note', 'refund', 'return') AND status = 'completed' THEN discount_amount END), 0) as disc_prev
                ")
                ->first();

            $sales = [
                'grossCur' => (float) $sales->gross_cur,
                'grossPrev' => (float) $sales->gross_prev,
                'refCur' => (float) $sales->ref_cur,
                'refPrev' => (float) $sales->ref_prev,
                'dueCur' => (float) $sales->due_cur,
                'duePrev' => (float) $sales->due_prev,
                'discCur' => (float) $sales->disc_cur,
                'discPrev' => (float) $sales->disc_prev,
            ];
        } catch (\Exception $e) {}

        try {
            $expCur = (float) DB::table('cash_movements')
                ->where('tenant_id', $tenantId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('movement_type', 'cash_out')
                ->whereRaw("date_trunc('month', created_at) = date_trunc('month', now())")
                ->sum('amount');
            $expPrev = (float) DB::table('cash_movements')
                ->where('tenant_id', $tenantId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('movement_type', 'cash_out')
                ->whereRaw("date_trunc('month', created_at) = date_trunc('month', now()) - interval '1 month'")
                ->sum('amount');
        } catch (\Exception $e) {
            $expCur = 0;
            $expPrev = 0;
        }

        try {
            $purchase = DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN date_trunc('month', purchase_date) = date_trunc('month', now()) AND status != 'returned' THEN grand_total END), 0) as pur_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', purchase_date) = date_trunc('month', now()) - interval '1 month' AND status != 'returned' THEN grand_total END), 0) as pur_prev,
                    COALESCE(SUM(CASE WHEN date_trunc('month', purchase_date) = date_trunc('month', now()) AND status = 'returned' THEN grand_total END), 0) as purret_cur,
                    COALESCE(SUM(CASE WHEN date_trunc('month', purchase_date) = date_trunc('month', now()) - interval '1 month' AND status = 'returned' THEN grand_total END), 0) as purret_prev
                ")
                ->first();

            $purchase = [
                'purchaseCur' => (float) $purchase->pur_cur,
                'purchasePrev' => (float) $purchase->pur_prev,
                'purchaseRetCur' => (float) $purchase->purret_cur,
                'purchaseRetPrev' => (float) $purchase->purret_prev,
            ];
        } catch (\Exception $e) {}

        $netCur = $sales['grossCur'] - $sales['refCur'] - $sales['discCur'];
        $netPrev = $sales['grossPrev'] - $sales['refPrev'] - $sales['discPrev'];
        $costs = ['costCur' => 0, 'costPrev' => 0];
        try {
            $costRows = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->where('ti.tenant_id', $tenantId)
                ->where('t.tenant_id', $tenantId)
                ->where('t.is_deleted', false)
                ->where('t.status', 'completed')
                ->whereNotIn('t.document_type', ['credit_note', 'refund', 'return'])
                ->when($branchId, fn ($q) => $q->where('t.branch_id', $branchId))
                ->when($cashier, fn ($q) => $q->where('t.cashier_id', $cashier))
                ->selectRaw("COALESCE(SUM(CASE WHEN date_trunc('month', t.transaction_date) = date_trunc('month', now()) THEN COALESCE(ti.cost_price, 0) * COALESCE(ti.quantity, 0) ELSE 0 END), 0) as cost_cur, COALESCE(SUM(CASE WHEN date_trunc('month', t.transaction_date) = date_trunc('month', now()) - interval '1 month' THEN COALESCE(ti.cost_price, 0) * COALESCE(ti.quantity, 0) ELSE 0 END), 0) as cost_prev")
                ->first();

            $costs = [
                'costCur' => (float) $costRows->cost_cur,
                'costPrev' => (float) $costRows->cost_prev,
            ];
        } catch (\Exception $e) {}

        $profitCur = $netCur - $costs['costCur'] - $expCur;
        $profitPrev = $netPrev - $costs['costPrev'] - $expPrev;

        return array_merge($sales, $purchase, [
            'expCur' => $expCur,
            'expPrev' => $expPrev,
            'netCur' => $netCur,
            'netPrev' => $netPrev,
            'profitCur' => $profitCur,
            'profitPrev' => $profitPrev,
            'payRetCur' => $sales['refCur'] + $purchase['purchaseRetCur'],
            'payRetPrev' => $sales['refPrev'] + $purchase['purchaseRetPrev'],
        ]);
    }
}