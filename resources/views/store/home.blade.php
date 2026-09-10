@extends('store.layout')

@section('title', 'Home')

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('store.home', $slug) }}">Home</a> / <span>All Products</span>
    </div>
    <div class="main-layout">
        {{-- Sidebar --}}
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Categories</div>
                <a href="{{ route('store.products', $slug) }}" class="sidebar-link {{ !request('category') ? 'active' : '' }}">
                    All Products <span class="badge">{{ $products->count() }}</span>
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('store.products', [$slug, 'category' => $cat['id']]) }}" class="sidebar-link {{ request('category') === $cat['id'] ? 'active' : '' }}">
                        {{ $cat['name'] }}
                    </a>
                @endforeach
            </div>

            @if($settings->contact_info)
                <div class="sidebar-section">
                    <div class="sidebar-title">Need Help?</div>
                    @if(!empty($settings->contact_info['phone']))
                        <p style="font-size:.85rem;color:#6b7280;margin-bottom:4px">📞 {{ $settings->contact_info['phone'] }}</p>
                    @endif
                    @if(!empty($settings->contact_info['email']))
                        <p style="font-size:.85rem;color:#6b7280">✉️ {{ $settings->contact_info['email'] }}</p>
                    @endif
                </div>
            @endif
        </aside>

        {{-- Products --}}
        <div class="content-area">
            <div class="content-header">
                <div>
                    <h1>All Products</h1>
                    <span class="result-count">{{ $products->count() }} {{ Str::plural('product', $products->count()) }} found</span>
                </div>
                <div class="sort-bar">
                    <a href="{{ route('store.products', array_merge([$slug], request()->query(), ['sort' => 'popular'])) }}" class="sort-btn {{ request('sort','popular') === 'popular' ? 'active' : '' }}">Popular</a>
                    <a href="{{ route('store.products', array_merge([$slug], request()->query(), ['sort' => 'newest'])) }}" class="sort-btn {{ request('sort') === 'newest' ? 'active' : '' }}">Newest</a>
                    <a href="{{ route('store.products', array_merge([$slug], request()->query(), ['sort' => 'price_low'])) }}" class="sort-btn {{ request('sort') === 'price_low' ? 'active' : '' }}">Price ↑</a>
                    <a href="{{ route('store.products', array_merge([$slug], request()->query(), ['sort' => 'price_high'])) }}" class="sort-btn {{ request('sort') === 'price_high' ? 'active' : '' }}">Price ↓</a>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="product-grid">
                    @foreach($products as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:60px 20px;background:#fff;border-radius:14px">
                    <p style="font-size:1.1rem;color:#86868b;font-weight:500">No products found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
