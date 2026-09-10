@extends('store.layout')

@section('title', 'Cart')

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('store.home', $slug) }}">Home</a> / <span>Shopping Cart</span>
    </div>

    @if(empty($items))
        <div style="text-align:center;padding:80px 20px;background:#fff;border-radius:16px">
            <svg style="width:64px;height:64px;margin:0 auto 16px;opacity:.3" fill="none" stroke="#86868b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            <p style="font-size:1.2rem;color:#86868b;font-weight:600;margin-bottom:8px">Your cart is empty</p>
            <p style="color:#c7c7cc;margin-bottom:20px">Looks like you haven't added anything yet</p>
            <a href="{{ route('store.products', $slug) }}" style="display:inline-block;padding:12px 32px;background:#6e3efb;color:#fff;border-radius:10px;font-weight:700">Browse Products</a>
        </div>
    @else
        <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
            <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
                <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:20px">Shopping Cart ({{ count($items) }})</h2>
                @foreach($items as $index => $item)
                    <div style="display:flex;gap:16px;align-items:center;padding:16px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6' : '' }}">
                        <div style="width:72px;height:72px;border-radius:10px;background:#f5f5f7;overflow:hidden;flex-shrink:0">
                            @if(!empty($item['image_url']))
                                <img src="{{ $item['image_url'] }}" style="width:100%;height:100%;object-fit:cover" alt="">
                            @endif
                        </div>
                        <div style="flex:1;min-width:0">
                            <p style="font-weight:600;font-size:.95rem">{{ $item['name'] }}</p>
                            @if(!empty($item['sale_unit_name']))
                                <p style="font-size:.8rem;color:#86868b">{{ $item['sale_unit_name'] }}</p>
                            @endif
                            <p style="font-size:.85rem;color:#86868b;margin-top:2px">{{ number_format($item['price'], 0) }} UGX</p>
                        </div>
                        <div style="display:flex;align-items:center;border:1px solid #e5e5ea;border-radius:8px;overflow:hidden">
                            <form action="{{ route('store.cart.update', $slug) }}" method="POST" style="display:flex">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}" style="width:32px;height:36px;border:none;background:#f5f5f7;cursor:pointer;font-size:.9rem">−</button>
                                <span style="width:36px;text-align:center;font-weight:600;font-size:.9rem;line-height:36px">{{ $item['quantity'] }}</span>
                                <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" style="width:32px;height:36px;border:none;background:#f5f5f7;cursor:pointer;font-size:.9rem">+</button>
                            </form>
                        </div>
                        <div style="text-align:right;min-width:100px">
                            <p style="font-weight:700;font-size:.95rem">{{ number_format($item['price'] * $item['quantity'], 0) }} UGX</p>
                            <form action="{{ route('store.cart.remove', $slug) }}" method="POST" style="margin-top:4px">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <button type="submit" style="background:none;border:none;color:#ff3b30;font-size:.8rem;cursor:pointer;font-weight:500">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary --}}
            <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);position:sticky;top:80px">
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px">Order Summary</h3>
                <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.9rem">
                    <span style="color:#86868b">Subtotal</span>
                    <span style="font-weight:600">{{ number_format($cart->total, 0) }} UGX</span>
                </div>
                <div style="border-top:1px solid #f3f4f6;margin:12px 0;padding-top:12px;display:flex;justify-content:space-between">
                    <span style="font-weight:700">Total</span>
                    <span style="font-weight:800;font-size:1.2rem;color:#1d1d1f">{{ number_format($cart->total, 0) }} UGX</span>
                </div>
                <a href="{{ route('store.checkout', $slug) }}" style="display:block;text-align:center;padding:14px;background:#6e3efb;color:#fff;border-radius:10px;font-weight:700;font-size:.95rem;margin-top:16px;transition:background .2s">
                    Proceed to Checkout
                </a>
                <a href="{{ route('store.products', $slug) }}" style="display:block;text-align:center;padding:12px;color:#6e3efb;font-weight:600;font-size:.88rem;margin-top:12px">
                    ← Continue Shopping
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
