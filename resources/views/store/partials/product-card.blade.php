<a href="{{ route('store.product', [$slug, $product['id']]) }}" class="product-card">
    <div class="img-wrap">
        @if(!empty($product['image_url']))
            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
        @else
            <span class="placeholder-img">No Image</span>
        @endif
        @if($product['in_stock'] ?? true)
            <div class="quick-add" onclick="event.preventDefault();document.getElementById('quickAdd{{ $product['id'] }}').submit();">
                <button type="submit">🛒 Add to Cart</button>
            </div>
            <form id="quickAdd{{ $product['id'] }}" action="{{ route('store.cart.add', $slug) }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                <input type="hidden" name="quantity" value="1">
            </form>
        @endif
    </div>
    <div class="info">
        @if(!empty($product['category_name']))
            <div class="category">{{ $product['category_name'] }}</div>
        @endif
        <div class="name">{{ $product['name'] }}</div>
        <div class="price-row">
            <span class="price">{{ number_format($product['price'], 0) }} UGX</span>
        </div>
        @if($product['track_stock'] ?? false)
            @if($product['in_stock'])
                <span class="stock-label stock-in">In Stock</span>
            @else
                <span class="stock-label stock-out">Out of Stock</span>
            @endif
        @endif
    </div>
</a>
