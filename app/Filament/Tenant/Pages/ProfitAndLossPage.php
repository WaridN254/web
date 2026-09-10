<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Reports\ReportLayoutConcern;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProfitAndLossPage extends Page
{
    use HasPermission;
    use ReportLayoutConcern;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';


    public static function getNavigationLabel(): string
    {
        return __('navigation.profit_loss');
    }


    protected static ?string $title = 'Profit & Loss';

    protected static ?string $slug = 'profit-and-loss';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reports');
    }


    protected static ?int $navigationSort = 18;

    protected string $view = 'filament.tenant.pages.reports.layout';

    public static function getSubtitle(): string
    {
        return 'Monthly profit & loss report';
    }

    protected function defaultFilters(): array
    {
        return ['from' => '', 'to' => ''];
    }

    public function getFilterFields(): array
    {
        return [
            ['type' => 'date', 'key' => 'from', 'label' => 'From Date'],
            ['type' => 'date', 'key' => 'to', 'label' => 'To Date'],
        ];
    }

    private function monthRange(): array
    {
        $from = $this->dateFrom();
        $to = $this->dateTo();

        $start = $from ? Carbon::parse($from)->startOfMonth() : now()->startOfMonth()->subMonths(11);
        $end = $to ? Carbon::parse($to)->startOfMonth() : now()->startOfMonth();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $months = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $months;
    }

    public function getReportColumns(): array
    {
        $months = $this->monthRange();
        $columns = [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
        ];
        foreach ($months as $m) {
            $columns[] = [
                'key' => $m,
                'label' => Carbon::parse($m . '-01')->format('M y'),
                'type' => 'money',
                'align' => 'right',
            ];
        }
        $columns[] = ['key' => 'total', 'label' => 'Total', 'type' => 'money', 'align' => 'right'];

        return $columns;
    }

    public function getReportRows(): array
    {
        $months = $this->monthRange();
        $start = Carbon::parse($months[0] . '-01')->startOfMonth();
        $end = Carbon::parse($months[count($months) - 1] . '-01')->endOfMonth();

        $income = $this->monthlyIncome($start, $end);
        $profit = $this->monthlyProfit($start, $end);
        $expenses = $this->monthlyExpenses($start, $end);

        $rowIncome = ['name' => 'Income', 'total' => 0];
        $rowProfit = ['name' => 'Gross Profit', 'total' => 0];
        $rowExpenses = ['name' => 'Expenses', 'total' => 0];
        $rowNet = ['name' => 'Net Profit', 'total' => 0];

        foreach ($months as $m) {
            $i = $income[$m] ?? 0;
            $p = $profit[$m] ?? 0;
            $e = $expenses[$m] ?? 0;
            $n = $p - $e;

            $rowIncome[$m] = $i;
            $rowProfit[$m] = $p;
            $rowExpenses[$m] = $e;
            $rowNet[$m] = $n;

            $rowIncome['total'] += $i;
            $rowProfit['total'] += $p;
            $rowExpenses['total'] += $e;
            $rowNet['total'] += $n;
        }

        return [$rowIncome, $rowProfit, $rowExpenses, $rowNet];
    }

    private function monthlyIncome(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('transactions')
            ->where('tenant_id', $this->tenantId())
            ->where('is_deleted', false)
            ->where('status', 'completed')
            ->whereNotIn('document_type', ['refund', 'return'])
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw("TO_CHAR(transaction_date, 'YYYY-MM') AS ym, SUM(total_amount) AS total")
            ->groupByRaw("TO_CHAR(transaction_date, 'YYYY-MM')")
            ->get();

        return $this->mapByMonth($rows);
    }

    private function monthlyProfit(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transaction_items.tenant_id', $this->tenantId())
            ->where('transactions.tenant_id', $this->tenantId())
            ->where('transactions.status', 'completed')
            ->where('transactions.is_deleted', false)
            ->whereBetween('transactions.transaction_date', [$start, $end])
            ->selectRaw("TO_CHAR(transactions.transaction_date, 'YYYY-MM') AS ym, SUM(transaction_items.line_total - (COALESCE(transaction_items.cost_price, 0) * transaction_items.quantity)) AS total")
            ->groupByRaw("TO_CHAR(transactions.transaction_date, 'YYYY-MM')")
            ->get();

        return $this->mapByMonth($rows);
    }

    private function monthlyExpenses(Carbon $start, Carbon $end): array
    {
        $expenses = [];

        $cash = DB::table('cash_movements')
            ->where('tenant_id', $this->tenantId())
            ->where(function ($q) {
                $q->whereIn('movement_type', ['cash_out', 'expense', 'out'])
                    ->orWhere('category', 'expense');
            })
            ->whereBetween('movement_date', [$start, $end])
            ->selectRaw("TO_CHAR(movement_date, 'YYYY-MM') AS ym, SUM(amount) AS total")
            ->groupByRaw("TO_CHAR(movement_date, 'YYYY-MM')")
            ->get();

        foreach ($this->mapByMonth($cash) as $m => $v) {
            $expenses[$m] = ($expenses[$m] ?? 0) + $v;
        }

        $purchase = DB::table('purchase_payments')
            ->where('tenant_id', $this->tenantId())
            ->whereBetween('payment_date', [$start, $end])
            ->selectRaw("TO_CHAR(payment_date, 'YYYY-MM') AS ym, SUM(amount) AS total")
            ->groupByRaw("TO_CHAR(payment_date, 'YYYY-MM')")
            ->get();

        foreach ($this->mapByMonth($purchase) as $m => $v) {
            $expenses[$m] = ($expenses[$m] ?? 0) + $v;
        }

        return $expenses;
    }

    private function mapByMonth($rows): array
    {
        $map = [];
        foreach ($rows as $r) {
            $map[$r->ym] = (float) $r->total;
        }

        return $map;
    }
}