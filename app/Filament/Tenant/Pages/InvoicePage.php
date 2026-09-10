<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Tenant;
use App\Models\Transaction;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class InvoicePage extends Page
{
    use HasPermission;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';


    public static function getNavigationLabel(): string
    {
        return __('navigation.invoices');
    }


    protected static ?string $title = 'Invoices';


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.sales');
    }


    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.invoice-page';

    public string $search = '';

    public ?string $customerFilter = null;

    public ?string $statusFilter = null;

    public ?string $branchFilter = null;

    public function getInvoices(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchService = app(\App\Services\BranchService::class);
        $canViewAll = (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
        $activeBranchId = $canViewAll ? null : $branchService->getActiveBranchId();

        $query = Transaction::query()
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.is_deleted', false)
            ->when($activeBranchId, fn ($q) => $q->where('transactions.branch_id', $activeBranchId))
            ->when($this->branchFilter, fn ($q) => $q->where('transactions.branch_id', $this->branchFilter));

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('transactions.receipt_number', 'ilike', '%' . $this->search . '%')
                    ->orWhere('transactions.customer_name', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->customerFilter) {
            $query->where('transactions.customer_id', $this->customerFilter);
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'paid') {
                $query->where('transactions.balance_due', '<=', 0);
            } elseif ($this->statusFilter === 'unpaid') {
                $query->where('transactions.balance_due', '>', 0);
            }
        }

        return $query
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->select('transactions.*', 'branches.name as branch_name')
            ->orderByDesc('transactions.transaction_date')
            ->limit(500)
            ->get()
            ->all();
    }

    public function getCustomers(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->orderBy('full_name')
            ->pluck('full_name', 'id')
            ->all();
    }

    public function getBranches(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return DB::table('branches')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function canViewAllBranches(): bool
    {
        return (bool) (auth()->user()?->can_view_all_branches ?? false)
            || in_array(auth()->user()?->role?->name ?? '', ['Owner', 'Admin'], true);
    }

    public function invoiceStatus(Transaction $tx): string
    {
        if ((float) ($tx->balance_due ?? 0) <= 0) {
            return 'Paid';
        }

        $date = $tx->transaction_date?->copy()->addDays(15);

        if ($date && $date->lt(now())) {
            return 'Overdue';
        }

        return 'Unpaid';
    }

    public function companyInfo(): array
    {
        $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
        $business = $tenant?->business;

        return [
            'name' => $business?->name ?? $tenant?->name ?? 'HALIS',
            'address' => $business?->address,
            'phone' => $business?->phone ?? $tenant?->admin_phone,
            'email' => $tenant?->admin_email ?? $business?->email,
            'currency' => $business?->currency_code ?? $tenant?->currency_code ?? 'UGX',
        ];
    }

    public function getInvoiceUrl(Transaction $tx): string
    {
        return '/tenant/invoice-details/' . $tx->id;
    }

    public function deleteInvoice(string $id): void
    {
        $tenantId = auth()->user()?->tenant_id;

        $tx = Transaction::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if ($tx) {
            $tx->update(['is_deleted' => true]);

            $this->dispatch('toast', ['type' => 'error', 'title' => 'Invoice deleted']);
        }
    }
}