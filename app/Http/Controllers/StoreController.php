<?php

namespace App\Http\Controllers;

use App\Services\OnlineStoreService;
use App\Services\OnlineCartService;
use App\Services\OnlineOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StoreController extends Controller
{
    protected function resolveTenant(string $slug): ?object
    {
        $settings = DB::table('online_store_settings')
            ->where('url_slug', $slug)
            ->first();

        if (!$settings) return null;

        $tenant = DB::table('tenants')->where('id', $settings->tenant_id)->first();
        return $tenant ? (object) ['tenant' => $tenant, 'settings' => $settings] : null;
    }

    protected function storeService(): OnlineStoreService
    {
        return app(OnlineStoreService::class);
    }

    public function index(string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);

        if (!$settings || !$settings->is_enabled) {
            abort(404, 'Online store is not available.');
        }

        $categories = $this->storeService()->getCategories($tenantId);
        $result = $this->storeService()->getStorefrontProducts($tenantId, null, null, 1, 12);

        return view('store.home', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $result['products'],
            'featured_products' => $result['products']->take(6),
            'slug' => $slug,
        ]);
    }

    public function products(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);

        if (!$settings || !$settings->is_enabled) {
            abort(404);
        }

        $categoryId = $request->get('category');
        $search = $request->get('search');
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->storeService()->getStorefrontProducts($tenantId, $categoryId, $search, $page, 12);
        $categories = $this->storeService()->getCategories($tenantId);

        return view('store.products', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $result['products'],
            'pagination' => $result,
            'activeCategory' => $categoryId,
            'search' => $search,
            'slug' => $slug,
        ]);
    }

    public function productDetail(string $slug, string $id)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);

        if (!$settings || !$settings->is_enabled) {
            abort(404);
        }

        $product = $this->storeService()->getStorefrontProduct($tenantId, $id);

        if (!$product) {
            abort(404);
        }

        return view('store.product-detail', [
            'settings' => $settings,
            'product' => $product,
            'slug' => $slug,
        ]);
    }

    public function cart(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $cart = $cartService->getCart($tenantId, null, $sessionId);
        $branches = $this->storeService()->getActiveBranches($tenantId);

        return view('store.cart', [
            'settings' => $settings,
            'cart' => $cart,
            'items' => $cart->items ?? [],
            'branches' => $branches,
            'slug' => $slug,
        ]);
    }

    public function addToCart(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $cartService->addItem(
            $tenantId,
            $request->product_id,
            (int) $request->get('quantity', 1),
            $request->get('variant_id'),
            $request->get('sale_unit_id'),
            null,
            $sessionId
        );

        return redirect()->route('store.cart', $slug)
            ->with('success', 'Item added to cart.');
    }

    public function updateCart(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $cartService->updateItemQuantity(
            $tenantId,
            (int) $request->index,
            (int) $request->quantity,
            null,
            $sessionId
        );

        return redirect()->route('store.cart', $slug);
    }

    public function removeFromCart(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $cartService->removeItem(
            $tenantId,
            (int) $request->index,
            null,
            $sessionId
        );

        return redirect()->route('store.cart', $slug);
    }

    public function checkout(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $items = $cartService->getCartItems($tenantId, null, $sessionId);

        if (empty($items)) {
            return redirect()->route('store.cart', $slug)->with('error', 'Cart is empty.');
        }

        $branches = $this->storeService()->getActiveBranches($tenantId);

        return view('store.checkout', [
            'settings' => $settings,
            'items' => $items,
            'total' => collect($items)->sum(fn ($i) => (float) $i['price'] * (float) $i['quantity']),
            'branches' => $branches,
            'slug' => $slug,
        ]);
    }

    public function placeOrder(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $items = $cartService->getCartItems($tenantId, null, $sessionId);

        if (empty($items)) {
            return redirect()->route('store.cart', $slug)->with('error', 'Cart is empty.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'delivery_method' => 'required|in:pickup,delivery',
            'branch_id' => 'required_if:delivery_method,pickup|nullable|string',
            'delivery_address' => 'required_if:delivery_method,delivery|nullable|string',
        ]);

        $deliveryAddress = null;
        if ($request->delivery_method === 'delivery') {
            $deliveryAddress = [
                'address' => $request->delivery_address,
                'city' => $request->get('city', ''),
                'district' => $request->get('district', ''),
            ];
        }

        try {
            $orderService = app(OnlineOrderService::class);
            $order = $orderService->createOrder(
                $tenantId,
                $items,
                [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                ],
                $request->delivery_method,
                $request->branch_id,
                $deliveryAddress,
                $request->notes,
                $request->payment_method,
                null
            );

            $cartService->clearCart($tenantId, null, $sessionId);

            return redirect()->route('store.order-tracking', [$slug, $order->order_number])
                ->with('success', 'Order placed successfully!');

        } catch (\RuntimeException $e) {
            return redirect()->route('store.checkout', $slug)
                ->with('error', $e->getMessage());
        }
    }

    public function orderTracking(string $slug, string $orderNumber)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) abort(404);

        $tenantId = $data->tenant->id;
        $settings = app(OnlineStoreService::class)->getSettings($tenantId);

        $order = \App\Models\OnlineOrder::where('tenant_id', $tenantId)
            ->where('order_number', $orderNumber)
            ->with('items', 'branch', 'statusHistory')
            ->first();

        if (!$order) {
            abort(404);
        }

        return view('store.order-tracking', [
            'order' => $order,
            'settings' => $settings,
            'slug' => $slug,
        ]);
    }

    public function cartCount(Request $request, string $slug)
    {
        $data = $this->resolveTenant($slug);
        if (!$data) return response()->json(['count' => 0]);

        $tenantId = $data->tenant->id;
        $sessionId = Session::getId();

        $cartService = app(OnlineCartService::class);
        $count = $cartService->getCartCount($tenantId, null, $sessionId);

        return response()->json(['count' => $count]);
    }
}
