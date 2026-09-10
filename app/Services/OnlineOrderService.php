<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Models\OnlineOrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineOrderService
{
    public function createOrder(
        string $tenantId,
        array $cartItems,
        array $customerInfo,
        string $deliveryMethod,
        ?string $branchId = null,
        ?array $deliveryAddress = null,
        ?string $notes = null,
        ?string $paymentMethod = null,
        ?string $customerId = null
    ): OnlineOrder {
        return DB::transaction(function () use ($tenantId, $cartItems, $customerInfo, $deliveryMethod, $branchId, $deliveryAddress, $notes, $paymentMethod, $customerId) {
            $storeService = app(OnlineStoreService::class);

            if (!$branchId) {
                $branchId = $storeService->determineFulfillmentBranch($tenantId, $cartItems);
            }

            if (!$branchId) {
                throw new \RuntimeException('No branch can fulfill this order. Please try different items or delivery method.');
            }

            $subtotal = 0;
            $taxAmount = 0;
            foreach ($cartItems as $item) {
                $lineTax = (float) $item['price'] * (float) $item['quantity'] * ((float) ($item['tax_rate'] ?? 0) / 100);
                $lineTotal = (float) $item['price'] * (float) $item['quantity'] + $lineTax;
                $subtotal += (float) $item['price'] * (float) $item['quantity'];
                $taxAmount += $lineTax;
            }

            $settings = $storeService->getSettings($tenantId);
            $deliveryFee = 0;
            if ($deliveryMethod === 'delivery' && $settings) {
                $deliveryFee = (float) $settings->delivery_fee;
                if ($settings->free_delivery_threshold && $subtotal >= (float) $settings->free_delivery_threshold) {
                    $deliveryFee = 0;
                }
            }

            $totalAmount = $subtotal + $taxAmount + $deliveryFee;
            $orderNumber = OnlineOrder::generateOrderNumber();

            $order = OnlineOrder::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'customer_name' => $customerInfo['name'] ?? 'Guest',
                'customer_email' => $customerInfo['email'] ?? null,
                'customer_phone' => $customerInfo['phone'] ?? null,
                'status' => 'pending',
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => 0,
                'delivery_fee' => round($deliveryFee, 2),
                'total_amount' => round($totalAmount, 2),
                'amount_paid' => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'delivery_method' => $deliveryMethod,
                'delivery_address' => $deliveryAddress,
                'notes' => $notes,
                'channel' => 'online',
            ]);

            foreach ($cartItems as $item) {
                $lineTax = (float) $item['price'] * (float) $item['quantity'] * ((float) ($item['tax_rate'] ?? 0) / 100);

                OnlineOrderItem::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'online_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['name'] ?? $item['product_name'] ?? 'Product',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => round($lineTax, 2),
                    'line_total' => round((float) $item['price'] * (float) $item['quantity'] + $lineTax, 2),
                    'sale_unit_id' => $item['sale_unit_id'] ?? null,
                    'sale_unit_name' => $item['sale_unit_name'] ?? null,
                    'unit_conversion_to_base' => $item['unit_conversion_to_base'] ?? 1,
                    'base_quantity' => $item['base_quantity'] ?? null,
                ]);
            }

            OnlineOrderStatusHistory::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'online_order_id' => $order->id,
                'status' => 'pending',
                'previous_status' => null,
                'notes' => 'Order placed online',
            ]);

            return $order->fresh();
        });
    }

    public function updateStatus(OnlineOrder $order, string $status, ?string $notes = null, ?string $changedBy = null): OnlineOrder
    {
        return DB::transaction(function () use ($order, $status, $notes, $changedBy) {
            $previousStatus = $order->status;

            $updates = ['status' => $status];
            if ($status === 'confirmed') $updates['confirmed_at'] = now();
            if ($status === 'shipped') $updates['shipped_at'] = now();
            if ($status === 'delivered') $updates['delivered_at'] = now();
            if ($status === 'cancelled') $updates['cancelled_at'] = now();

            $order->update($updates);

            if (in_array($status, ['confirmed', 'shipped']) && $order->payment_status === 'unpaid') {
                $order->update(['payment_status' => 'paid', 'amount_paid' => $order->total_amount]);
            }

            if ($status === 'confirmed') {
                $this->deductStock($order);
            }

            if ($status === 'cancelled' && in_array($previousStatus, ['confirmed', 'processing'])) {
                $this->restoreStock($order);
            }

            OnlineOrderStatusHistory::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $order->tenant_id,
                'online_order_id' => $order->id,
                'status' => $status,
                'previous_status' => $previousStatus,
                'notes' => $notes,
                'changed_by' => $changedBy,
            ]);

            return $order->fresh();
        });
    }

    public function markPaid(OnlineOrder $order, string $paymentMethod, float $amount): OnlineOrder
    {
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'amount_paid' => $amount,
        ]);

        return $order->fresh();
    }

    private function deductStock(OnlineOrder $order): void
    {
        $inventoryService = app(InventoryService::class);

        foreach ($order->items as $item) {
            $qty = (float) $item->quantity * (float) $item->unit_conversion_to_base;

            if ($item->sale_unit_id && $item->unit_conversion_to_base > 1) {
                // Use base quantity if available
                if ($item->base_quantity) {
                    $qty = (float) $item->base_quantity;
                }
            }

            $inventoryService->decrementStock(
                $item->product_id,
                $order->branch_id,
                $qty,
                $item->variant_id,
                $order->tenant_id
            );
        }
    }

    private function restoreStock(OnlineOrder $order): void
    {
        $inventoryService = app(InventoryService::class);

        foreach ($order->items as $item) {
            $qty = (float) $item->quantity * (float) $item->unit_conversion_to_base;

            if ($item->sale_unit_id && $item->unit_conversion_to_base > 1) {
                if ($item->base_quantity) {
                    $qty = (float) $item->base_quantity;
                }
            }

            $inventoryService->incrementStock(
                $item->product_id,
                $order->branch_id,
                $qty,
                $item->variant_id,
                $order->tenant_id
            );
        }
    }

    public function getOrders(string $tenantId, ?string $status = null, ?string $branchId = null, int $page = 1, int $perPage = 20): array
    {
        $query = OnlineOrder::where('tenant_id', $tenantId)
            ->where('is_deleted', false);

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $total = $query->count();
        $orders = $query->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function getOrderStats(string $tenantId, ?string $branchId = null): array
    {
        $query = OnlineOrder::where('tenant_id', $tenantId)->where('is_deleted', false);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $today = now()->startOfDay();

        return [
            'total_orders' => $query->count(),
            'pending_orders' => (clone $query)->where('status', 'pending')->count(),
            'processing_orders' => (clone $query)->where('status', 'processing')->count(),
            'shipped_orders' => (clone $query)->where('status', 'shipped')->count(),
            'delivered_orders' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $query)->where('status', 'cancelled')->count(),
            'today_orders' => (clone $query)->where('created_at', '>=', $today)->count(),
            'today_revenue' => (float) (clone $query)->where('created_at', '>=', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'total_revenue' => (float) $query->where('payment_status', 'paid')->sum('total_amount'),
        ];
    }
}
