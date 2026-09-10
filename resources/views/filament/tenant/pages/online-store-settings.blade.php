<x-filament-panels::page>
    <form wire:submit="save">
        <div style="display:flex;flex-direction:column;gap:24px">

            <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                    <h2 style="font-size:1.3rem;font-weight:800;color:#1a1a2e;margin-bottom:2px">Online Store Settings</h2>
                    <p style="font-size:.85rem;color:#64748b">Configure your public storefront, delivery, and checkout experience</p>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    @if($is_enabled && $url_slug)
                        <a href="{{ url('/' . $url_slug) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:.85rem;font-weight:600;color:#166534;text-decoration:none">
                            View Store ↗
                        </a>
                    @endif
                    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:inherit">
                        Save Settings
                    </button>
                </div>
            </div>

            {{-- Store Status --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;background:{{ $is_enabled ? '#f0fdf4' : '#fef2f2' }};border-radius:10px;display:flex;align-items:center;justify-content:center">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $is_enabled ? '#22c55e' : '#ef4444' }};box-shadow:0 0 8px {{ $is_enabled ? 'rgba(34,197,94,.4)' : 'rgba(239,68,68,.4)' }}"></div>
                        </div>
                        <div>
                            <h3 style="font-size:.95rem;font-weight:700;color:#1a1a2e">Store Status</h3>
                            <p style="font-size:.8rem;color:#64748b">{{ $is_enabled ? 'Live and accepting orders' : 'Offline — customers cannot place orders' }}</p>
                        </div>
                    </div>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none">
                        <span style="font-size:.85rem;font-weight:600;color:{{ $is_enabled ? '#16a34a' : '#94a3b8' }}">{{ $is_enabled ? 'Active' : 'Disabled' }}</span>
                        <div style="position:relative">
                            <input type="checkbox" wire:model="is_enabled" style="opacity:0;width:0;height:0;position:absolute">
                            <div wire:click="$set('is_enabled', {{ $is_enabled ? 'false' : 'true' }})" style="width:48px;height:26px;border-radius:13px;background:{{ $is_enabled ? '#22c55e' : '#d1d5db' }};cursor:pointer;transition:background .2s;position:relative">
                                <div style="width:20px;height:20px;border-radius:50%;background:#fff;position:absolute;top:3px;{{ $is_enabled ? 'right:3px' : 'left:3px' }};transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Store Details --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem">🏪</div>
                    <div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1a1a2e">Store Details</h3>
                        <p style="font-size:.8rem;color:#64748b">Basic information about your online store</p>
                    </div>
                </div>
                <div style="padding:24px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Store Name</label>
                            <input type="text" wire:model="store_name" placeholder="My Online Store"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                        </div>
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">URL Slug</label>
                            <div style="display:flex;align-items:stretch">
                                <span style="padding:10px 12px;background:#f8fafc;border:1px solid #d1d5db;border-right:none;border-radius:8px 0 0 8px;font-size:.82rem;color:#64748b;white-space:nowrap;display:flex;align-items:center">{{ url('/') }}/</span>
                                <input type="text" wire:model="url_slug" placeholder="my-store"
                                    style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:0 8px 8px 0;font-size:.9rem;font-family:inherit">
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:4px">Lowercase letters, numbers, and hyphens only</div>
                        </div>
                        <div style="grid-column:span 2">
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Store Description</label>
                            <textarea wire:model="store_description" rows="2" placeholder="Tell customers what your store is about..."
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;resize:vertical;font-family:inherit"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery & Fulfillment --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem">🚚</div>
                    <div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1a1a2e">Delivery &amp; Fulfillment</h3>
                        <p style="font-size:.8rem;color:#64748b">Configure how orders are fulfilled and delivered</p>
                    </div>
                </div>
                <div style="padding:24px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Auto-assign Orders To</label>
                            <select wire:model="fulfillment_strategy"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;background:#fff;font-family:inherit">
                                <option value="branch_with_stock">Branch with Stock</option>
                                <option value="highest_stock">Highest Stock Branch</option>
                                <option value="manual">Manual Assignment</option>
                            </select>
                        </div>
                        <div></div>
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="allow_pickup" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Allow Pickup</div>
                                <div style="font-size:.78rem;color:#64748b">Customers collect from branch</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="allow_delivery" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Allow Delivery</div>
                                <div style="font-size:.78rem;color:#64748b">Deliver to customer address</div>
                            </div>
                        </label>
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Delivery Fee</label>
                            <input type="number" wire:model="delivery_fee" step="0.01" placeholder="0"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                        </div>
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Free Delivery Threshold</label>
                            <input type="number" wire:model="free_delivery_threshold" step="0.01" placeholder="No minimum"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:4px">Leave empty to disable free delivery</div>
                        </div>
                        <div style="grid-column:span 2">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                                <input type="checkbox" wire:model="allow_branch_selection" style="width:18px;height:18px;accent-color:#6366f1">
                                <div>
                                    <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Let Customer Choose Branch</div>
                                    <div style="font-size:.78rem;color:#64748b">Show branch selector during checkout</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Checkout --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:#f3e8ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem">🛒</div>
                    <div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1a1a2e">Checkout</h3>
                        <p style="font-size:.8rem;color:#64748b">Control the checkout experience for customers</p>
                    </div>
                </div>
                <div style="padding:24px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="tax_included" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Prices Include Tax</div>
                                <div style="font-size:.78rem;color:#64748b">Tax calculated into displayed price</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="allow_guest_checkout" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Guest Checkout</div>
                                <div style="font-size:.78rem;color:#64748b">No account required to order</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="require_phone" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Require Phone Number</div>
                                <div style="font-size:.78rem;color:#64748b">Phone required at checkout</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                            <input type="checkbox" wire:model="show_stock_levels" style="width:18px;height:18px;accent-color:#6366f1">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a2e">Show Stock Levels</div>
                                <div style="font-size:.78rem;color:#64748b">Display remaining stock on product pages</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:#fce7f3;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem">📞</div>
                    <div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1a1a2e">Contact Information</h3>
                        <p style="font-size:.8rem;color:#64748b">How customers can reach you</p>
                    </div>
                </div>
                <div style="padding:24px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Phone</label>
                            <input type="text" wire:model="contact_phone" placeholder="+256 700 000000"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                        </div>
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Email</label>
                            <input type="email" wire:model="contact_email" placeholder="store@example.com"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                        </div>
                        <div style="grid-column:span 2">
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Address</label>
                            <input type="text" wire:model="contact_address" placeholder="Street address, city"
                                style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Store URL Banner --}}
            @if($url_slug)
            <div style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);border:1px solid #c7d2fe;border-radius:12px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:#6366f1;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem">🌐</div>
                    <div>
                        <div style="font-size:.82rem;font-weight:600;color:#3730a3">Your store is live at</div>
                        <a href="{{ url('/' . $url_slug) }}" target="_blank" style="font-size:1rem;font-weight:700;color:#4f46e5;text-decoration:underline;text-underline-offset:2px">{{ url('/' . $url_slug) }}</a>
                    </div>
                </div>
                <a href="{{ url('/' . $url_slug) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#6366f1;color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none">
                    Open Store ↗
                </a>
            </div>
            @endif

            {{-- Sticky Save Bar --}}
            <div style="position:sticky;bottom:0;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border:1px solid #e2e8f0;border-radius:12px;padding:16px 24px;display:flex;justify-content:flex-end;gap:10px;box-shadow:0 -4px 12px rgba(0,0,0,.05)">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 28px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:inherit">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
