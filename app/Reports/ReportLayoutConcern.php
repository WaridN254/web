<?php

namespace App\Reports;

trait ReportLayoutConcern
{
    public array $filters = [];

    public int $page = 1;

    public int $perPage = 10;

    public function mountReportLayout(): void
    {
        $this->filters = $this->defaultFilters();
    }

    public function mount(): void
    {
        $this->mountReportLayout();
    }

    protected function defaultFilters(): array
    {
        return [];
    }

    public function tenantId(): ?string
    {
        return auth()->user()?->tenant_id;
    }

    public function generateReport(): void
    {
        $this->page = 1;
    }

    public function prevPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function paginateRows(array $rows): array
    {
        $total = count($rows);
        $perPage = max(1, $this->perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $this->page), $pages);
        $this->page = $page;
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $to = min($page * $perPage, $total);

        return [
            'rows' => array_slice(array_values($rows), ($page - 1) * $perPage, $perPage),
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $pages,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public static function getSubtitle(): string
    {
        return '';
    }

    public function tenantCurrency(): string
    {
        $tenant = \App\Models\Tenant::query()->find($this->tenantId());

        return $tenant?->business?->currency_code ?? $tenant?->currency_code ?? 'UGX';
    }

    public function getFilterFields(): array
    {
        return [];
    }

    public function getStatCards(): array
    {
        return [];
    }

    public function getNavPills(): array
    {
        return [];
    }

    public function getReportColumns(): array
    {
        return [];
    }

    public function getReportRows(): array
    {
        return [];
    }

    public function getTotalsRow(): ?array
    {
        return null;
    }

    public function getTableTitle(): string
    {
        return static::getNavigationLabel() ?? static::getTitle() ?? 'Report';
    }

    public function getEmptyText(): string
    {
        return 'No data found.';
    }

    public function filterValue(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    protected function dateFrom(): ?string
    {
        $value = $this->filterValue('from');

        return filled($value) ? $value : null;
    }

    protected function dateTo(): ?string
    {
        $value = $this->filterValue('to');

        return filled($value) ? $value : null;
    }

    public function canViewAllBranches(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (in_array($user->role?->name ?? '', ['Owner', 'Admin'], true)) {
            return true;
        }

        return (bool) ($user->can_view_all_branches ?? false);
    }

    public function activeBranchId(): ?string
    {
        if ($this->canViewAllBranches()) {
            return $this->filterValue('branch_id') ?: null;
        }

        return app(\App\Services\BranchService::class)->getActiveBranchId();
    }

    protected function scopeQueryToBranch($query, string $branchColumn = 'branch_id'): void
    {
        $branchId = $this->activeBranchId();

        if ($branchId) {
            $query->where($branchColumn, $branchId);
        }
    }

    public function getBranchFilterField(): array
    {
        if (! $this->canViewAllBranches()) {
            return [];
        }

        $tenantId = $this->tenantId();

        return [
            [
                'type' => 'select',
                'key' => 'branch_id',
                'label' => 'Branch',
                'options' => ['' => 'All Branches'] + \App\Models\Branch::query()
                    ->where('tenant_id', $tenantId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray(),
            ],
        ];
    }

    protected function betweenClause(string $column, string $from, string $to)
    {
        return function ($query) use ($column, $from, $to) {
            $query->where($column, '>=', $from . ' 00:00:00');
            if (filled($to)) {
                $query->where($column, '<=', $to . ' 23:59:59');
            }
        };
    }

    protected function isPillActive(string $url): bool
    {
        $current = rtrim('/' . request()->path(), '/');

        if (filled(request()->getQueryString())) {
            $current .= '?' . request()->getQueryString();
        }

        return rtrim($current, '/') === rtrim($url, '/');
    }

    public function companyName(): string
    {
        $tenant = \App\Models\Tenant::query()->find($this->tenantId());

        return $tenant?->business?->name ?? $tenant?->name ?? 'HALIS';
    }
}