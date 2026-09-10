<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $tenant = DB::table('tenants')->orderBy('created_at')->first();

        if (! $tenant) {
            return view('tenant.dashboard', [
                'tenant' => null,
                'userName' => 'Administrator',
                'currency' => 'UGX',
                'period' => $this->resolvePeriod($request)[0],
                'dateLabel' => $this->formatDateLabel(now()->startOfMonth(), now()->endOfMonth()),
                'stats' => $this->emptyStats(),
                'recentTransactions' => collect(),
                'topCustomers' => collect(),
                'topCategories' => collect(),
                'movement' => [],
            ]);
        }

        $tenantId = $tenant->id;
        $period = $request->query('period', 'month');
        [$period, $start, $end, $previousStart, $previousEnd] = $this->resolvePeriod($request);

        $cacheKey = "tenant:{$tenantId}:dashboard_page";

        $data = Cache::remember($cacheKey, 300, function () use ($tenantId, $start, $end, $previousStart, $previousEnd) {
            return $this->computeData($tenantId, $start, $end, $previousStart, $previousEnd);
        });

        $settings = DB::table('settings')->where('tenant_id', $tenantId)->first();
        $user = DB::table('users')->where('tenant_id', $tenantId)->orderBy('created_at')->first();

        $stats = $data['stats'];
        $stats['transactionsToday'] = [
            'label' => 'Sales transactions today',
            'value' => $this->transactionsFor($tenantId, Carbon::today(), Carbon::today()->endOfDay())->count(),
            'isCount' => true,
        ];

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'userName' => $user->full_name ?? $user->name ?? 'Administrator',
            'currency' => $settings->currency_code ?? $tenant->currency_code ?? 'UGX',
            'period' => $period,
            'dateLabel' => $this->formatDateLabel($start, $end),
            'stats' => $stats,
            'recentTransactions' => $data['recentTransactions'],
            'topCustomers' => $data['topCustomers'],
            'topCategories' => $data['topCategories'],
            'movement' => $data['movement'],
        ]);
    }

    private function computeData(string $tenantId, Carbon $start, Carbon $end, Carbon $previousStart, Carbon $previousEnd): array
    {
        $current = $this->totalsFor($tenantId, $start, $end);
        $previous = $this->totalsFor($tenantId, $previousStart, $previousEnd);

        return [
            'stats' => [
                'netRevenue' => [
                    'label' => 'Net Revenue',
                    'value' => $current['netRevenue'],
                    'delta' => $this->percentChange($current['netRevenue'], $previous['netRevenue']),
                    'tone' => 'orange',
                ],
                'refunds' => [
                    'label' => 'Refunds / Returns',
                    'value' => $current['refunds'],
                    'delta' => $this->percentChange($current['refunds'], $previous['refunds']),
                    'tone' => 'blue',
                ],
                'grossRevenue' => [
                    'label' => 'Gross Revenue',
                    'value' => $current['grossRevenue'],
                    'delta' => $this->percentChange($current['grossRevenue'], $previous['grossRevenue']),
                    'tone' => 'teal',
                ],
                'discounts' => [
                    'label' => 'Discounts',
                    'value' => $current['discounts'],
                    'delta' => $this->percentChange($current['discounts'], $previous['discounts']),
                    'tone' => 'indigo',
                ],
                'profit' => [
                    'label' => 'Profit',
                    'value' => $current['profit'],
                    'delta' => $this->percentChange($current['profit'], $previous['profit']),
                ],
                'invoiceDue' => [
                    'label' => 'Invoice Due',
                    'value' => $current['invoiceDue'],
                    'delta' => $this->percentChange($current['invoiceDue'], $previous['invoiceDue']),
                ],
                'expenses' => [
                    'label' => 'Total Expenses',
                    'value' => $current['expenses'],
                    'delta' => $this->percentChange($current['expenses'], $previous['expenses']),
                ],
                'customers' => [
                    'label' => 'Customers',
                    'value' => $current['customers'],
                    'delta' => $this->percentChange($current['customers'], $previous['customers']),
                    'isCount' => true,
                ],
            ],
            'recentTransactions' => $this->recentTransactions($tenantId),
            'topCustomers' => $this->topCustomers($tenantId, $start, $end),
            'topCategories' => $this->topCategories($tenantId, $start, $end),
            'movement' => $this->movement($tenantId, $start, $end),
        ];
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->query('period', 'month');
        $now = Carbon::now();

        [$start, $end] = match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => $this->customRange($request, $now),
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $period = in_array($period, ['today', 'week', 'month', 'year', 'custom'], true) ? $period : 'month';
        $durationInDays = max(1, $start->diffInDays($end) + 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($durationInDays - 1)->startOfDay();

        return [$period, $start, $end, $previousStart, $previousEnd];
    }

    private function customRange(Request $request, Carbon $fallback): array
    {
        try {
            $start = Carbon::parse($request->query('from'))->startOfDay();
            $end = Carbon::parse($request->query('to'))->endOfDay();

            if ($start->lessThanOrEqualTo($end)) {
                return [$start, $end];
            }
        } catch (\Throwable) {
            //
        }

        return [$fallback->copy()->startOfMonth(), $fallback->copy()->endOfMonth()];
    }

    private function totalsFor(string $tenantId, Carbon $start, Carbon $end): array
    {
        $sales = $this->transactionsFor($tenantId, $start, $end)
            ->whereNotIn('document_type', ['refund', 'return'])
            ->where('document_type', '!=', 'credit_note')
            ->where('status', 'completed');

        $refunds = (float) $this->transactionsFor($tenantId, $start, $end)
            ->whereIn('document_type', ['refund', 'return'])
            ->sum('total_amount');

        $cashExpenses = (float) DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->whereBetween('movement_date', [$start, $end])
            ->where(function ($query) {
                $query->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->sum('amount');

        $purchasePayments = (float) DB::table('purchase_payments')
            ->where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        return [
            'netRevenue' => (float) (clone $sales)->sum('total_amount'),
            'grossRevenue' => (float) (clone $sales)
                ->selectRaw('COALESCE(SUM(total_amount + COALESCE(discount_amount, 0)), 0) as gross_revenue')
                ->value('gross_revenue'),
            'discounts' => (float) (clone $sales)->sum('discount_amount'),
            'refunds' => $refunds,
            'profit' => $this->profitFor($tenantId, $start, $end),
            'invoiceDue' => (float) $this->transactionsFor($tenantId, $start, $end)
                ->whereNotIn('document_type', ['credit_note', 'refund', 'return'])
                ->where('status', '!=', 'refunded')
                ->where('settlement_status', '!=', 'paid')
                ->sum('balance_due'),
            'expenses' => $cashExpenses + $purchasePayments,
            'customers' => DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('is_deleted', false)
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    private function transactionsFor(string $tenantId, Carbon $start, Carbon $end)
    {
        return DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->whereBetween('transaction_date', [$start, $end]);
    }

    private function profitFor(string $tenantId, Carbon $start, Carbon $end): float
    {
        $grossProfit = (float) DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transaction_items.tenant_id', $tenantId)
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->whereNotIn('transactions.document_type', ['credit_note', 'refund', 'return'])
            ->where('transactions.is_deleted', false)
            ->whereBetween('transactions.transaction_date', [$start, $end])
            ->sum(DB::raw('COALESCE(transaction_items.line_total, 0) - COALESCE(transaction_items.discount, 0) - (COALESCE(transaction_items.cost_price, 0) * COALESCE(transaction_items.quantity, 0))'));

        $expenses = (float) DB::table('cash_movements')
            ->where('tenant_id', $tenantId)
            ->whereBetween('movement_date', [$start, $end])
            ->where(function ($query) {
                $query->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->sum('amount');

        return $grossProfit - $expenses;
    }

    private function recentTransactions(string $tenantId)
    {
        return DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->orderByDesc('transaction_date')
            ->limit(6)
            ->get(['receipt_number', 'customer_name', 'status', 'total_amount', 'transaction_date']);
    }

    private function topCustomers(string $tenantId, Carbon $start, Carbon $end)
    {
        return $this->transactionsFor($tenantId, $start, $end)
            ->selectRaw("COALESCE(NULLIF(customer_name, ''), 'Walk-in Customer') as name, SUM(total_amount) as total")
            ->where('status', 'completed')
            ->groupByRaw("COALESCE(NULLIF(customer_name, ''), 'Walk-in Customer')")
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    private function topCategories(string $tenantId, Carbon $start, Carbon $end)
    {
        return DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('transaction_items.tenant_id', $tenantId)
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->where('transactions.is_deleted', false)
            ->whereBetween('transactions.transaction_date', [$start, $end])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(transaction_items.line_total) as total")
            ->groupByRaw("COALESCE(categories.name, 'Uncategorized')")
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    private function movement(string $tenantId, Carbon $start, Carbon $end): array
    {
        $midpoint = $start->copy()->addDays((int) floor(max(1, $start->diffInDays($end)) / 2));

        return [
            [
                'label' => 'Recent period',
                'value' => (float) $this->transactionsFor($tenantId, $midpoint, $end)->sum('total_amount'),
            ],
            [
                'label' => 'Earlier period',
                'value' => (float) $this->transactionsFor($tenantId, $start, $midpoint->copy()->subSecond())->sum('total_amount'),
            ],
        ];
    }

    private function percentChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current === 0.0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    private function formatDateLabel(Carbon $start, Carbon $end): string
    {
        return $start->format('m/d/Y').' - '.$end->format('m/d/Y');
    }

    private function emptyStats(): array
    {
        return [
            'netRevenue' => ['label' => 'Net Revenue', 'value' => 0, 'delta' => 0, 'tone' => 'orange'],
            'refunds' => ['label' => 'Refunds / Returns', 'value' => 0, 'delta' => 0, 'tone' => 'blue'],
            'grossRevenue' => ['label' => 'Gross Revenue', 'value' => 0, 'delta' => 0, 'tone' => 'teal'],
            'discounts' => ['label' => 'Discounts', 'value' => 0, 'delta' => 0, 'tone' => 'indigo'],
            'profit' => ['label' => 'Profit', 'value' => 0, 'delta' => 0],
            'invoiceDue' => ['label' => 'Invoice Due', 'value' => 0, 'delta' => 0],
            'expenses' => ['label' => 'Total Expenses', 'value' => 0, 'delta' => 0],
            'customers' => ['label' => 'Customers', 'value' => 0, 'delta' => 0, 'isCount' => true],
            'transactionsToday' => ['label' => 'Sales transactions today', 'value' => 0, 'isCount' => true],
        ];
    }
}
