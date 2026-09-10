@extends('store.layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('store.home', $slug) }}">Home</a> /
        <span>Order {{ $order->order_number }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">
        {{-- Left --}}
        <div>
            {{-- Header --}}
            <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:12px">
                    <div>
                        <h1 style="font-size:1.4rem;font-weight:700">Order {{ $order->order_number }}</h1>
                        <p style="font-size:.85rem;color:#86868b;margin-top:2px">{{ $order->created_at->format('d M, Y \a\t h:i A') }}</p>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span style="display:inline-block;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:700;
                            background:{{ match($order->status) {
                                'pending' => '#fef3c7',
                                'confirmed' => '#dbeafe',
                                'shipped' => '#ede9fe',
                                'delivered' => '#d1fae5',
                                'cancelled' => '#fee2e2',
                                default => '#f3f4f6',
                            } }};
                            color:{{ match($order->status) {
                                'pending' => '#92400e',
                                'confirmed' => '#1e40af',
                                'shipped' => '#7c3aed',
                                'delivered' => '#065f46',
                                'cancelled' => '#991b1b',
                                default => '#374151',
                            } }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span style="display:inline-block;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:600;
                            background:{{ $order->payment_status === 'paid' ? '#d1fae5' : '#fee2e2' }};
                            color:{{ $order->payment_status === 'paid' ? '#065f46' : '#991b1b' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:16px">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Items</h3>
                @foreach($order->items as $item)
                    <div style="display:flex;gap:14px;align-items:center;padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6' : '' }}">
                        <div style="width:56px;height:56px;border-radius:10px;background:#f5f5f7;flex-shrink:0"></div>
                        <div style="flex:1;min-width:0">
                            <p style="font-weight:600;font-size:.9rem">{{ $item->product_name }}</p>
                            @if($item->variant_id)
                                <p style="font-size:.8rem;color:#86868b">Variant</p>
                            @endif
                        </div>
                        <div style="text-align:right">
                            <p style="font-size:.85rem;color:#86868b">{{ number_format($item->unit_price, 0) }} × {{ $item->quantity }}</p>
                            <p style="font-weight:700;font-size:.9rem">{{ number_format($item->line_total, 0) }} UGX</p>
                        </div>
                    </div>
                @endforeach

                <div style="border-top:1px solid #e5e5ea;margin-top:12px;padding-top:12px">
                    <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                        <span style="color:#86868b">Subtotal</span>
                        <span>{{ number_format($order->subtotal, 0) }} UGX</span>
                    </div>
                    @if($order->tax_amount > 0)
                        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                            <span style="color:#86868b">Tax</span>
                            <span>{{ number_format($order->tax_amount, 0) }} UGX</span>
                        </div>
                    @endif
                    @if($order->delivery_fee > 0)
                        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                            <span style="color:#86868b">Delivery</span>
                            <span>{{ number_format($order->delivery_fee, 0) }} UGX</span>
                        </div>
                    @endif
                    <div style="border-top:1px solid #e5e5ea;margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;font-weight:700;font-size:1.05rem">
                        <span>Total</span>
                        <span style="color:#6e3efb">{{ number_format($order->total_amount, 0) }} UGX</span>
                    </div>
                </div>
            </div>

            {{-- Activity --}}
            @if($order->statusHistory->count() > 0)
                <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Activity</h3>
                    @foreach($order->statusHistory->sortByDesc('created_at') as $i => $history)
                        <div style="display:flex;gap:14px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6' : '' }}">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $i === 0 ? '#6e3efb' : '#d1d5db' }};margin-top:6px;flex-shrink:0"></div>
                            <div style="flex:1">
                                <div style="display:flex;justify-content:space-between">
                                    <span style="font-weight:600;font-size:.9rem">{{ ucfirst($history->status) }}</span>
                                    <span style="font-size:.8rem;color:#86868b">{{ $history->created_at->format('d M, H:i') }}</span>
                                </div>
                                @if($history->notes)
                                    <p style="font-size:.85rem;color:#86868b;margin-top:2px">{{ $history->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Right: Customer Info --}}
        <div>
            <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04);position:sticky;top:80px">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Customer Info</h3>
                <p style="font-weight:700;font-size:.95rem">{{ $order->customer_name }}</p>
                @if($order->customer_phone)
                    <p style="font-size:.88rem;color:#6b7280;margin-top:2px">📞 {{ $order->customer_phone }}</p>
                @endif
                @if($order->customer_email)
                    <p style="font-size:.88rem;color:#6b7280">✉️ {{ $order->customer_email }}</p>
                @endif

                <div style="border-top:1px solid #f3f4f6;margin:16px 0;padding-top:16px">
                    <p style="font-size:.75rem;text-transform:uppercase;color:#86868b;font-weight:600;margin-bottom:8px">Delivery</p>
                    <p style="font-weight:600;font-size:.9rem">{{ ucfirst($order->delivery_method) }}</p>
                    @if($order->branch)
                        <p style="font-size:.85rem;color:#6b7280;margin-top:2px">{{ $order->branch->name }}</p>
                    @endif
                    @if(!empty($order->delivery_address['address']))
                        <p style="font-size:.85rem;color:#6b7280;margin-top:2px">{{ $order->delivery_address['address'] }}</p>
                    @endif
                </div>

                <a href="{{ route('store.home', $slug) }}" style="display:block;text-align:center;padding:12px;background:#f5f5f7;color:#48484a;border-radius:10px;font-weight:600;font-size:.88rem;margin-top:16px;transition:background .2s">
                    ← Back to Store
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
