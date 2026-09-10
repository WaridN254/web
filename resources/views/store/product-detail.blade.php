@extends('store.layout')

@section('title', $product['name'])

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('store.home', $slug) }}">Home</a> /
        @if(!empty($product['category_name']))
            <a href="{{ route('store.products', [$slug, 'category' => $product['category_id']]) }}">{{ $product['category_name'] }}</a> /
        @endif
        <span>{{ $product['name'] }}</span>
    </div>

    <div style="background:#fff;border-radius:16px;padding:32px;margin-bottom:32px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start">
            {{-- Left: Image --}}
            <div>
                <div style="background:#f5f5f7;border-radius:14px;height:420px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:12px" id="mainImageWrap">
                    @if(!empty($product['image_url']))
                        <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" style="width:100%;height:100%;object-fit:cover" id="mainImage">
                    @else
                        <span style="color:#c7c7cc;font-size:1.2rem">No Image</span>
                    @endif
                </div>
            </div>

            {{-- Right: Info --}}
            <div>
                @if(!empty($product['category_name']))
                    <p style="font-size:.8rem;text-transform:uppercase;color:#86868b;letter-spacing:.5px;font-weight:600;margin-bottom:6px">{{ $product['category_name'] }}</p>
                @endif
                <h1 style="font-size:1.8rem;font-weight:700;color:#1d1d1f;line-height:1.3;margin-bottom:8px">{{ $product['name'] }}</h1>

                @if(!empty($product['sku']))
                    <p style="font-size:.85rem;color:#86868b;margin-bottom:16px">SKU: {{ $product['sku'] }}</p>
                @endif

                <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:16px">
                    <span style="font-size:2rem;font-weight:800;color:#1d1d1f">{{ number_format($product['price'], 0) }} UGX</span>
                </div>

                @if($product['track_stock'] ?? false)
                    @if($product['in_stock'])
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:20px">
                            <span style="width:8px;height:8px;border-radius:50%;background:#34c759;display:inline-block"></span>
                            <span style="font-size:.9rem;color:#166534;font-weight:600">In Stock ({{ number_format($product['stock_quantity'] ?? 0) }} available)</span>
                        </div>
                    @else
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:20px">
                            <span style="width:8px;height:8px;border-radius:50%;background:#ff3b30;display:inline-block"></span>
                            <span style="font-size:.9rem;color:#991b1b;font-weight:600">Out of Stock</span>
                        </div>
                    @endif
                @endif

                @if(!empty($product['description']))
                    <div style="font-size:.92rem;color:#48484a;line-height:1.7;margin-bottom:24px">{!! nl2br(e($product['description'])) !!}</div>
                @endif

                @if(!empty($product['available_branches']) && count($product['available_branches']) > 0)
                    <div style="margin-bottom:24px;background:#f9fafb;border-radius:10px;padding:14px">
                        <p style="font-size:.8rem;text-transform:uppercase;color:#86868b;font-weight:600;margin-bottom:8px">Available at</p>
                        @foreach($product['available_branches'] as $b)
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:.88rem;{{ !$loop->last ? 'border-bottom:1px solid #e5e5ea' : '' }}">
                                <span style="font-weight:500">{{ $b['branch_name'] }}</span>
                                <span style="color:#86868b">{{ number_format($b['quantity']) }} units</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add to Cart --}}
                @if($product['in_stock'] ?? true)
                    <form action="{{ route('store.cart.add', $slug) }}" method="POST" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                        <div style="display:flex;align-items:center;border:1px solid #e5e5ea;border-radius:10px;overflow:hidden">
                            <button type="button" onclick="let q=document.getElementById('qty');q.value=Math.max(1,parseInt(q.value)-1)" style="width:40px;height:44px;border:none;background:#f5f5f7;font-size:1.1rem;cursor:pointer">−</button>
                            <input type="number" id="qty" name="quantity" value="1" min="1" style="width:50px;height:44px;text-align:center;border:none;font-size:.95rem;font-weight:600;outline:none">
                            <button type="button" onclick="let q=document.getElementById('qty');q.value=parseInt(q.value)+1" style="width:40px;height:44px;border:none;background:#f5f5f7;font-size:1.1rem;cursor:pointer">+</button>
                        </div>
                        <button type="submit" style="flex:1;padding:14px 32px;background:#6e3efb;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:8px">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            Add to Cart
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
