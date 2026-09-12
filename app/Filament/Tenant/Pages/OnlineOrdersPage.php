<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\OnlineOrder;
use App\Services\OnlineOrderService;
use Filament\Pages\Page;

class OnlineOrdersPage extends Page
{
    use HasPermission;

    protected static ?string $title = 'All Orders';
    protected static ?string $slug = 'online-orders-page';
    protected string $view = 'filament.tenant.pages.online-orders';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationLabel(): string
    {
        return 'Online Orders';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Online Shop';
    }

    public array $orders = [];
    public string $activeTab = 'all';
    public string $search = '';
    public string $sort = 'newest';
    public ?string $selectedOrderId = null;
    public ?array $selectedOrder = null;
    public string $newStatus = '';

    public int $allCount = 0;
    public int $pendingCount = 0;
    public int $confirmedCount = 0;
    public int $shippedCount = 0;
    public int $deliveredCount = 0;
    public int $cancelledCount = 0;

    public function mount(): void
    {
        $this->loadCounts();
        $this->loadOrders();
    }

    public function updatedSearch(): void
    {
        $this->loadOrders();
    }

    public function updatedSort(): void
    {
        $this->loadOrders();
    }

    public function updatedActiveTab(): void
    {
        $this->loadOrders();
    }

    public function loadCounts(): void
    {
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $base = OnlineOrder::where('tenant_id', $tenantId)->where('is_deleted', false);

        $this->allCount = (clone $base)->count();
        $this->pendingCount = (clone $base)->where('status', 'pending')->count();
        $this->confirmedCount = (clone $base)->where('status', 'confirmed')->count();
        $this->shippedCount = (clone $base)->where('status', 'shipped')->count();
        $this->deliveredCount = (clone $base)->where('status', 'delivered')->count();
        $this->cancelledCount = (clone $base)->where('status', 'cancelled')->count();
    }

    public function loadOrders(): void
    {
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $query = OnlineOrder::where('tenant_id', $tenantId)
            ->where('is_deleted', false)
            ->with('branch');

        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'ilike', "%{$s}%")
                  ->orWhere('customer_name', 'ilike', "%{$s}%")
                  ->orWhere('customer_phone', 'ilike', "%{$s}%");
            });
        }

        $query->orderByDesc('created_at');

        $this->orders = $query->limit(50)->get()->toArray();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->selectedOrderId = null;
        $this->selectedOrder = null;
    }

    public function viewOrder(string $orderId): void
    {
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $order = OnlineOrder::where('tenant_id', $tenantId)
            ->where('id', $orderId)
            ->with('items', 'branch', 'statusHistory')
            ->first();

        if (!$order) return;

        $this->selectedOrder = $order->toArray();
        $this->selectedOrderId = $orderId;
        $this->newStatus = $order->status;
    }

    public function closeOrder(): void
    {
        $this->selectedOrder = null;
        $this->selectedOrderId = null;
        $this->newStatus = '';
    }

    public function updateOrderStatus(): void
    {
        if (!$this->selectedOrderId || !$this->newStatus) return;

        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $order = OnlineOrder::where('tenant_id', $tenantId)
            ->where('id', $this->selectedOrderId)
            ->first();

        if (!$order) return;

        try {
            app(OnlineOrderService::class)
                ->updateStatus($order, $this->newStatus, 'Updated by ' . (auth()->user()->full_name ?? 'Admin'));

            $this$this->dispatch('gooey-toast', [
                'type' => 'success',
                'title' => 'Order Updated',
                'description' => "Order {$order->order_number} marked as {$this->newStatus}.",
            ]);

            $this->loadCounts();
            $this->loadOrders();
            $this->closeOrder();
        } catch (\Exception $e) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Error',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function cancelOrder(string $orderId): void
    {
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        $order = OnlineOrder::where('tenant_id', $tenantId)
            ->where('id', $orderId)
            ->first();

        if (!$order) return;

        try {
            app(OnlineOrderService::class)
                ->updateStatus($order, 'cancelled', 'Cancelled by ' . (auth()->user()->full_name ?? 'Admin'));

            $this$this->dispatch('gooey-toast', [
                'type' => 'success',
                'title' => 'Order Cancelled',
                'description' => "Order {$order->order_number} has been cancelled.",
            ]);

            $this->loadCounts();
            $this->loadOrders();
            $this->closeOrder();
        } catch (\Exception $e) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Error',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function getStatusColor(string $status): string
    {
        return match($status) {
            'pending' => '#f59e0b',
            'confirmed' => '#3b82f6',
            'shipped' => '#8b5cf6',
            'delivered' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    public function getStatusBg(string $status): string
    {
        return match($status) {
            'pending' => '#fef3c7',
            'confirmed' => '#dbeafe',
            'shipped' => '#ede9fe',
            'delivered' => '#d1fae5',
            'cancelled' => '#fee2e2',
            default => '#f3f4f6',
        };
    }
}
