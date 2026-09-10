@extends('store.layout')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('store.home', $slug) }}">Home</a> /
        <a href="{{ route('store.cart', $slug) }}">Cart</a> /
        <span>Checkout</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 400px;gap:28px;align-items:start">
        {{-- Left: Form --}}
        <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:24px">Checkout</h2>
            <form action="{{ route('store.checkout.place', $slug) }}" method="POST">
                @csrf

                <div style="margin-bottom:24px">
                    <p style="font-size:.8rem;text-transform:uppercase;color:#86868b;font-weight:600;margin-bottom:12px">Contact Information</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div>
                            <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Full Name *</label>
                            <input type="text" name="customer_name" required style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none;transition:border .2s" onfocus="this.style.borderColor='#6e3efb'" onblur="this.style.borderColor='#e5e5ea'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Phone *</label>
                            <input type="text" name="customer_phone" required style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none;transition:border .2s" onfocus="this.style.borderColor='#6e3efb'" onblur="this.style.borderColor='#e5e5ea'">
                        </div>
                    </div>
                    <div style="margin-top:12px">
                        <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Email (optional)</label>
                        <input type="email" name="customer_email" style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none;transition:border .2s" onfocus="this.style.borderColor='#6e3efb'" onblur="this.style.borderColor='#e5e5ea'">
                    </div>
                </div>

                <div style="margin-bottom:24px">
                    <p style="font-size:.8rem;text-transform:uppercase;color:#86868b;font-weight:600;margin-bottom:12px">Delivery Method</p>
                    <div style="display:flex;gap:12px;margin-bottom:16px">
                        <label id="pickupOpt" style="flex:1;padding:16px;border:2px solid #6e3efb;border-radius:12px;cursor:pointer;text-align:center;background:#f5f0ff;transition:all .2s">
                            <input type="radio" name="delivery_method" value="pickup" checked style="display:none" onclick="toggleDelivery(false)">
                            <div style="font-size:1.5rem;margin-bottom:4px">📦</div>
                            <div style="font-weight:700;font-size:.9rem">Pickup</div>
                            <div style="font-size:.8rem;color:#86868b">Collect from store</div>
                        </label>
                        @if($settings->allow_delivery)
                            <label id="deliveryOpt" style="flex:1;padding:16px;border:2px solid #e5e5ea;border-radius:12px;cursor:pointer;text-align:center;transition:all .2s">
                                <input type="radio" name="delivery_method" value="delivery" style="display:none" onclick="toggleDelivery(true)">
                                <div style="font-size:1.5rem;margin-bottom:4px">🚚</div>
                                <div style="font-weight:700;font-size:.9rem">Delivery</div>
                                <div style="font-size:.8rem;color:#86868b">To your address</div>
                            </label>
                        @endif
                    </div>

                    <div id="pickupFields">
                        <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Pickup Branch *</label>
                        <select name="branch_id" style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;background:#fff">
                            <option value="">Select a branch...</option>
                            @foreach($branches as $b)
                                <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="deliveryFields" style="display:none">
                        <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Delivery Address *</label>
                        <textarea name="delivery_address" rows="3" placeholder="Full address..." style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none;resize:vertical"></textarea>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
                            <div>
                                <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">City</label>
                                <input type="text" name="city" style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none">
                            </div>
                            <div>
                                <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">District</label>
                                <input type="text" name="district" style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:24px">
                    <p style="font-size:.8rem;text-transform:uppercase;color:#86868b;font-weight:600;margin-bottom:12px">Payment</p>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Payment Method</label>
                    <select name="payment_method" style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;background:#fff">
                        <option value="cash">Cash on Pickup/Delivery</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div style="margin-bottom:24px">
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#48484a;margin-bottom:4px">Notes (optional)</label>
                    <textarea name="notes" rows="2" placeholder="Special instructions..." style="width:100%;padding:12px;border:1px solid #e5e5ea;border-radius:10px;font-size:.92rem;outline:none;resize:vertical"></textarea>
                </div>

                <button type="submit" style="width:100%;padding:16px;background:#6e3efb;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s">
                    Place Order — {{ number_format($total, 0) }} UGX
                </button>
            </form>
        </div>

        {{-- Right: Summary --}}
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);position:sticky;top:80px">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Order Summary</h3>
            <div style="max-height:300px;overflow-y:auto">
                @foreach($items as $item)
                    <div style="display:flex;gap:12px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6' : '' }}">
                        <div style="width:48px;height:48px;border-radius:8px;background:#f5f5f7;overflow:hidden;flex-shrink:0">
                            @if(!empty($item['image_url']))
                                <img src="{{ $item['image_url'] }}" style="width:100%;height:100%;object-fit:cover" alt="">
                            @endif
                        </div>
                        <div style="flex:1;min-width:0">
                            <p style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item['name'] }}</p>
                            <p style="font-size:.8rem;color:#86868b">× {{ $item['quantity'] }}</p>
                        </div>
                        <span style="font-weight:600;font-size:.85rem;white-space:nowrap">{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                    </div>
                @endforeach
            </div>
            <div style="border-top:1px solid #e5e5ea;margin-top:12px;padding-top:12px;display:flex;justify-content:space-between">
                <span style="font-weight:700">Total</span>
                <span style="font-weight:800;font-size:1.15rem;color:#1d1d1f">{{ number_format($total, 0) }} UGX</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleDelivery(isDelivery) {
    document.getElementById('pickupFields').style.display = isDelivery ? 'none' : 'block';
    document.getElementById('deliveryFields').style.display = isDelivery ? 'block' : 'none';
    document.getElementById('pickupOpt').style.borderColor = isDelivery ? '#e5e5ea' : '#6e3efb';
    document.getElementById('pickupOpt').style.background = isDelivery ? 'transparent' : '#f5f0ff';
    if (document.getElementById('deliveryOpt')) {
        document.getElementById('deliveryOpt').style.borderColor = isDelivery ? '#6e3efb' : '#e5e5ea';
        document.getElementById('deliveryOpt').style.background = isDelivery ? '#f5f0ff' : 'transparent';
    }
}
</script>
@endpush
@endsection
