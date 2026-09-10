<?php

namespace App\Services;

use App\Models\OnlineCart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineCartService
{
    public function getCart(string $tenantId, ?string $customerId = null, ?string $sessionId = null): OnlineCart
    {
        $query = OnlineCart::where('tenant_id', $tenantId);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $cart = $query->first();

        if (!$cart) {
            $cart = OnlineCart::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'session_id' => $sessionId,
                'items' => [],
                'total' => 0,
            ]);
        }

        return $cart;
    }

    public function addItem(
        string $tenantId,
        string $productId,
        int $quantity = 1,
        ?string $variantId = null,
        ?string $saleUnitId = null,
        ?string $customerId = null,
        ?string $sessionId = null
    ): OnlineCart {
        $cart = $this->getCart($tenantId, $customerId, $sessionId);
        $items = $cart->items ?? [];

        $product = Product::where('id', $productId)->where('tenant_id', $tenantId)->first();
        if (!$product) {
            throw new \RuntimeException('Product not found.');
        }

        $price = (float) $product->billing_price;
        $unitConversion = 1;
        $saleUnitName = null;

        if ($saleUnitId && $product->saleUnits()->count() > 0) {
            $unit = $product->saleUnits()->get()->where('id', $saleUnitId)->first();
            if ($unit) {
                $price = (float) $unit->price;
                $unitConversion = (float) $unit->conversion_to_base_unit;
                $saleUnitName = $unit->unit_name;
            }
        }

        $existingIndex = null;
        foreach ($items as $i => $item) {
            if ($item['product_id'] === $productId
                && ($item['variant_id'] ?? null) === $variantId
                && ($item['sale_unit_id'] ?? null) === $saleUnitId
            ) {
                $existingIndex = $i;
                break;
            }
        }

        if ($existingIndex !== null) {
            $items[$existingIndex]['quantity'] += $quantity;
        } else {
            $items[] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $product->name . ($variantId ? ' — ' . ($product->variants->find($variantId)?->combination_name ?? '') : ''),
                'price' => $price,
                'cost_price' => (float) $product->cost_price,
                'tax_rate' => (float) $product->tax_rate,
                'quantity' => $quantity,
                'sale_unit_id' => $saleUnitId,
                'sale_unit_name' => $saleUnitName,
                'unit_conversion_to_base' => $unitConversion,
                'image_url' => $product->imageUrl(),
            ];
        }

        $total = collect($items)->sum(fn ($item) => (float) $item['price'] * (float) $item['quantity']);
        $cart->update(['items' => $items, 'total' => round($total, 2)]);

        return $cart->fresh();
    }

    public function updateItemQuantity(
        string $tenantId,
        int $itemIndex,
        int $quantity,
        ?string $customerId = null,
        ?string $sessionId = null
    ): OnlineCart {
        $cart = $this->getCart($tenantId, $customerId, $sessionId);
        $items = $cart->items ?? [];

        if (!isset($items[$itemIndex])) {
            throw new \RuntimeException('Cart item not found.');
        }

        if ($quantity <= 0) {
            unset($items[$itemIndex]);
            $items = array_values($items);
        } else {
            $items[$itemIndex]['quantity'] = $quantity;
        }

        $total = collect($items)->sum(fn ($item) => (float) $item['price'] * (float) $item['quantity']);
        $cart->update(['items' => $items, 'total' => round($total, 2)]);

        return $cart->fresh();
    }

    public function removeItem(
        string $tenantId,
        int $itemIndex,
        ?string $customerId = null,
        ?string $sessionId = null
    ): OnlineCart {
        return $this->updateItemQuantity($tenantId, $itemIndex, 0, $customerId, $sessionId);
    }

    public function clearCart(string $tenantId, ?string $customerId = null, ?string $sessionId = null): void
    {
        $cart = $this->getCart($tenantId, $customerId, $sessionId);
        $cart->update(['items' => [], 'total' => 0]);
    }

    public function getCartItems(string $tenantId, ?string $customerId = null, ?string $sessionId = null): array
    {
        $cart = $this->getCart($tenantId, $customerId, $sessionId);
        return $cart->items ?? [];
    }

    public function getCartCount(string $tenantId, ?string $customerId = null, ?string $sessionId = null): int
    {
        $items = $this->getCartItems($tenantId, $customerId, $sessionId);
        return collect($items)->sum('quantity');
    }
}
