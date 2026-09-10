<x-filament-panels::page>
    <style>
        .orders-search { display:flex;gap:12px;align-items:center;margin-bottom:4px;flex-wrap:wrap; }
        .orders-search input { flex:1;min-width:200px;padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;background:#f9fafb; }
        .orders-search select { padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;background:#f9fafb; }
        .orders-tabs { display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:24px; }
        .orders-tab { padding:12px 20px;font-size:.9rem;font-weight:600;color:#6b7280;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;white-space:nowrap; }
        .orders-tab:hover { color:#2563eb; }
        .orders-tab.active { color:#2563eb;border-bottom-color:#2563eb; }
        .orders-tab .count { background:#f3f4f6;padding:2px 8px;border-radius:10px;font-size:.75rem;margin-left:6px;font-weight:700; }
        .orders-tab.active .count { background:#dbeafe;color:#2563eb; }
        .order-card { background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:12px;overflow:hidden;transition:box-shadow .2s; }
        .order-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .order-card-header { display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f3f4f6; }
        .order-card-body { padding:16px 20px; }
        .order-item { display:flex;gap:14px;align-items:center;padding:10px 0; }
        .order-item-img { width:56px;height:56px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0; }
        .order-item-img img { width:100%;height:100%;object-fit:cover; }
        .order-status-badge { display:inline-block;padding:4px 12px;border-radius:6px;font-size:.8rem;font-weight:600; }
        .order-action-btn { padding:6px 14px;border-radius:6px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;transition:all .15s; }
        .order-action-btn.confirm { background:#2563eb;color:#fff; }
        .order-action-btn.confirm:hover { background:#1d4ed8; }
        .order-action-btn.cancel { background:#fff;color:#ef4444;border:1px solid #fecaca; }
        .order-action-btn.cancel:hover { background:#fef2f2; }
        .order-action-btn.view { background:#f3f4f6;color:#374151; }
        .order-action-btn.view:hover { background:#e5e7eb; }
        .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:flex;align-items:center;justify-content:center;padding:20px; }
        .modal-content { background:#fff;border-radius:16px;max-width:640px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3); }
        .modal-header { padding:24px 28px 16px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:start; }
        .modal-body { padding:24px 28px; }
        .modal-footer { padding:16px 28px 24px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap; }
    </style>

    {{-- Tabs --}}
    <div class="orders-tabs">
        <div class="orders-tab {{ $activeTab === 'all' ? 'active' : '' }}" wire:click="setTab('all')">
            🛒 All Orders <span class="count">{{ $allCount }}</span>
        </div>
        <div class="orders-tab {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
            ⏳ Pending <span class="count">{{ $pendingCount }}</span>
        </div>
        <div class="orders-tab {{ $activeTab === 'confirmed' ? 'active' : '' }}" wire:click="setTab('confirmed')">
            ✅ Confirmed <span class="count">{{ $confirmedCount }}</span>
        </div>
        <div class="orders-tab {{ $activeTab === 'shipped' ? 'active' : '' }}" wire:click="setTab('shipped')">
            🚚 Shipped <span class="count">{{ $shippedCount }}</span>
        </div>
        <div class="orders-tab {{ $activeTab === 'delivered' ? 'active' : '' }}" wire:click="setTab('delivered')">
            📦 Delivered <span class="count">{{ $deliveredCount }}</span>
        </div>
        <div class="orders-tab {{ $activeTab === 'cancelled' ? 'active' : '' }}" wire:click="setTab('cancelled')">
            ❌ Cancelled <span class="count">{{ $cancelledCount }}</span>
        </div>
    </div>

    {{-- Search & Sort --}}
    <div class="orders-search" style="margin-bottom:20px">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by order #, customer name, phone...">
        <select wire:model.live="sort">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="highest">Highest Amount</option>
            <option value="lowest">Lowest Amount</option>
        </select>
    </div>

    {{-- Orders --}}
    @if(empty($orders))
        <div style="text-align:center;padding:60px 20px;color:#9ca3af">
            <svg style="width:48px;height:48px;margin:0 auto 12px;opacity:.4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p style="font-size:1rem;font-weight:500">No orders found</p>
            <p style="font-size:.85rem;margin-top:4px">Orders from your online store will appear here</p>
        </div>
    @else
        @foreach($orders as $order)
            <div class="order-card">
                <div class="order-card-header">
                    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                        <div>
                            <span style="font-weight:700;font-size:.95rem;color:#111827">{{ $order['order_number'] }}</span>
                            <span style="margin-left:8px;font-size:.85rem;color:#9ca3af">{{ \Carbon\Carbon::parse($order['created_at'])->format('d M, Y') }}</span>
                        </div>
                        <span class="order-status-badge" style="background:{{ $this->getStatusBg($order['status']) }};color:{{ $this->getStatusColor($order['status']) }}">
                            {{ ucfirst($order['status']) }}
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-weight:700;font-size:1rem;color:#111827">{{ number_format($order['total_amount'], 0) }} UGX</span>
                        <span style="font-size:.8rem;color:#9ca3af;background:#f3f4f6;padding:4px 8px;border-radius:4px">{{ $order['payment_method'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="order-card-body">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                        <div style="flex:1;min-width:200px">
                            <p style="font-size:.8rem;color:#9ca3af;margin-bottom:4px">Customer</p>
                            <p style="font-weight:600;font-size:.9rem">{{ $order['customer_name'] }}</p>
                            @if($order['customer_phone'])
                                <p style="font-size:.85rem;color:#6b7280">📞 {{ $order['customer_phone'] }}</p>
                            @endif
                            @if($order['customer_email'])
                                <p style="font-size:.85rem;color:#6b7280">✉️ {{ $order['customer_email'] }}</p>
                            @endif
                        </div>
                        <div style="flex:1;min-width:200px">
                            <p style="font-size:.8rem;color:#9ca3af;margin-bottom:4px">Fulfillment</p>
                            <p style="font-weight:600;font-size:.9rem">{{ $order['branch']['name'] ?? 'Auto-assign' }}</p>
                            <p style="font-size:.85rem;color:#6b7280">{{ ucfirst($order['delivery_method']) }}</p>
                            @if(!empty($order['delivery_address']['address']))
                                <p style="font-size:.85rem;color:#6b7280">{{ $order['delivery_address']['address'] }}</p>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px;flex-shrink:0">
                            @if($order['status'] === 'pending')
                                <button wire:click="viewOrder('{{ $order['id'] }}')" class="order-action-btn view">View Details</button>
                                <button wire:click="cancelOrder('{{ $order['id'] }}')" class="order-action-btn cancel" onclick="return confirm('Cancel this order?')">Cancel Order</button>
                            @elseif($order['status'] === 'confirmed')
                                <button wire:click="viewOrder('{{ $order['id'] }}')" class="order-action-btn view">View Details</button>
                            @else
                                <button wire:click="viewOrder('{{ $order['id'] }}')" class="order-action-btn view">View Details</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Order Detail Modal --}}
    @if($selectedOrder)
        <div class="modal-overlay" wire:click="closeOrder">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <div>
                        <h2 style="font-size:1.25rem;font-weight:700;color:#111827">Order {{ $selectedOrder['order_number'] }}</h2>
                        <p style="font-size:.85rem;color:#9ca3af;margin-top:2px">{{ \Carbon\Carbon::parse($selectedOrder['created_at'])->format('d M, Y \a\t h:i A') }}</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <span class="order-status-badge" style="background:{{ $this->getStatusBg($selectedOrder['status']) }};color:{{ $this->getStatusColor($selectedOrder['status']) }};font-size:.85rem">
                            {{ ucfirst($selectedOrder['status']) }}
                        </span>
                        <button wire:click="closeOrder" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.5rem;line-height:1">&times;</button>
                    </div>
                </div>

                <div class="modal-body">
                    {{-- Customer & Delivery --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
                        <div style="background:#f9fafb;border-radius:10px;padding:16px">
                            <p style="font-size:.75rem;text-transform:uppercase;color:#9ca3af;font-weight:600;margin-bottom:8px">Customer Info</p>
                            <p style="font-weight:700;font-size:.95rem">{{ $selectedOrder['customer_name'] }}</p>
                            @if($selectedOrder['customer_phone'])
                                <p style="font-size:.85rem;color:#6b7280;margin-top:2px">{{ $selectedOrder['customer_phone'] }}</p>
                            @endif
                            @if($selectedOrder['customer_email'])
                                <p style="font-size:.85rem;color:#6b7280">{{ $selectedOrder['customer_email'] }}</p>
                            @endif
                        </div>
                        <div style="background:#f9fafb;border-radius:10px;padding:16px">
                            <p style="font-size:.75rem;text-transform:uppercase;color:#9ca3af;font-weight:600;margin-bottom:8px">Fulfillment</p>
                            <p style="font-weight:700;font-size:.95rem">{{ $selectedOrder['branch']['name'] ?? 'Auto-assign' }}</p>
                            <p style="font-size:.85rem;color:#6b7280;margin-top:2px">{{ ucfirst($selectedOrder['delivery_method']) }}</p>
                            @if(!empty($selectedOrder['delivery_address']['address']))
                                <p style="font-size:.85rem;color:#6b7280">{{ $selectedOrder['delivery_address']['address'] }}</p>
                                @if(!empty($selectedOrder['delivery_address']['city']))
                                    <p style="font-size:.85rem;color:#6b7280">{{ $selectedOrder['delivery_address']['city'] }}, {{ $selectedOrder['delivery_address']['district'] ?? '' }}</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Items --}}
                    <div style="margin-bottom:24px">
                        <p style="font-size:.75rem;text-transform:uppercase;color:#9ca3af;font-weight:600;margin-bottom:12px">Items ({{ count($selectedOrder['items']) }})</p>
                        @foreach($selectedOrder['items'] as $item)
                            <div style="display:flex;gap:14px;align-items:center;padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6' : '' }}">
                                <div class="order-item-img">
                                    @if(!empty($item['product_name']))
                                        <span style="font-size:.7rem;color:#9ca3af;text-align:center;line-height:1.2">{{ \Illuminate\Support\Str::limit($item['product_name'], 12) }}</span>
                                    @endif
                                </div>
                                <div style="flex:1;min-width:0">
                                    <p style="font-weight:600;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item['product_name'] }}</p>
                                    @if($item['variant_id'])
                                        <p style="font-size:.8rem;color:#6b7280">Variant</p>
                                    @endif
                                </div>
                                <div style="text-align:right">
                                    <p style="font-size:.85rem;color:#6b7280">{{ number_format($item['unit_price'], 0) }} × {{ $item['quantity'] }}</p>
                                    <p style="font-weight:700;font-size:.9rem">{{ number_format($item['line_total'], 0) }} UGX</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Summary --}}
                    <div style="background:#f9fafb;border-radius:10px;padding:16px">
                        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                            <span style="color:#6b7280">Subtotal</span>
                            <span>{{ number_format($selectedOrder['subtotal'], 0) }} UGX</span>
                        </div>
                        @if($selectedOrder['tax_amount'] > 0)
                            <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                                <span style="color:#6b7280">Tax</span>
                                <span>{{ number_format($selectedOrder['tax_amount'], 0) }} UGX</span>
                            </div>
                        @endif
                        @if($selectedOrder['delivery_fee'] > 0)
                            <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem">
                                <span style="color:#6b7280">Delivery Fee</span>
                                <span>{{ number_format($selectedOrder['delivery_fee'], 0) }} UGX</span>
                            </div>
                        @endif
                        @if($selectedOrder['discount_amount'] > 0)
                            <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem;color:#059669">
                                <span>Discount</span>
                                <span>-{{ number_format($selectedOrder['discount_amount'], 0) }} UGX</span>
                            </div>
                        @endif
                        <div style="border-top:1px solid #e5e7eb;margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;font-weight:700;font-size:1.05rem">
                            <span>Total</span>
                            <span style="color:#2563eb">{{ number_format($selectedOrder['total_amount'], 0) }} UGX</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.85rem;color:#6b7280;margin-top:4px">
                            <span>Payment: {{ ucfirst($selectedOrder['payment_method'] ?? 'N/A') }}</span>
                            <span class="order-status-badge" style="background:{{ $selectedOrder['payment_status'] === 'paid' ? '#d1fae5' : '#fee2e2' }};color:{{ $selectedOrder['payment_status'] === 'paid' ? '#065f46' : '#991b1b' }}">
                                {{ ucfirst($selectedOrder['payment_status']) }}
                            </span>
                        </div>
                    </div>

                    @if($selectedOrder['notes'])
                        <div style="margin-top:16px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px">
                            <p style="font-size:.8rem;font-weight:600;color:#92400e;margin-bottom:4px">Notes</p>
                            <p style="font-size:.85rem;color:#78350f">{{ $selectedOrder['notes'] }}</p>
                        </div>
                    @endif

                    {{-- Status History --}}
                    @if(!empty($selectedOrder['status_history']) && count($selectedOrder['status_history']) > 0)
                        <div style="margin-top:20px">
                            <p style="font-size:.75rem;text-transform:uppercase;color:#9ca3af;font-weight:600;margin-bottom:8px">Activity</p>
                            @foreach(collect($selectedOrder['status_history'])->sortByDesc('created_at') as $hist)
                                <div style="display:flex;gap:10px;padding:6px 0;font-size:.85rem">
                                    <span style="color:#9ca3af;white-space:nowrap">{{ \Carbon\Carbon::parse($hist['created_at'])->format('d M, H:i') }}</span>
                                    <span style="font-weight:600">{{ ucfirst($hist['status']) }}</span>
                                    @if($hist['notes'])
                                        <span style="color:#6b7280">— {{ $hist['notes'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <div style="display:flex;gap:8px;align-items:center;margin-right:auto">
                        <label style="font-size:.85rem;color:#6b7280;font-weight:600">Status:</label>
                        <select wire:model="newStatus" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    @if($newStatus !== $selectedOrder['status'])
                        <button wire:click="updateOrderStatus" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer">
                            Update Status
                        </button>
                    @endif
                    <button wire:click="closeOrder" style="padding:8px 20px;background:#f3f4f6;color:#374151;border:none;border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
