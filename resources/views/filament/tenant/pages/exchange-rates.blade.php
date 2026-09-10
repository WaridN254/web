<div style="max-width: 900px; margin: 0 auto; padding: 24px;">

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Exchange Rates</h1>
        <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Manage currency exchange rates for multi-currency transactions.</p>
    </div>

    {{-- Add / Edit Form --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">
            {{ $editingId ? 'Edit Exchange Rate' : 'Add Exchange Rate' }}
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">From Currency</label>
                <select wire:model="base_currency" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($currencies as $cur)
                        <option value="{{ $cur['code'] }}">{{ $cur['code'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">To Currency</label>
                <select wire:model="target_currency" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($currencies as $cur)
                        <option value="{{ $cur['code'] }}">{{ $cur['code'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Rate</label>
                <input type="number" step="any" wire:model="rate_value" placeholder="e.g. 3750" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" />
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Provider</label>
                <select wire:model="provider" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    <option value="manual">Manual</option>
                    <option value="api">API</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button wire:click="saveRate" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;">
                    {{ $editingId ? 'Update' : 'Add' }}
                </button>
                @if($editingId)
                    <button wire:click="cancelEdit" style="padding: 8px 16px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Cancel</button>
                @endif
            </div>
        </div>
    </div>

    {{-- Rates Table --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">From</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">To</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">Rate</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">Provider</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">Effective At</th>
                    <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rates as $rate)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-weight: 600;">{{ $rate['base_currency'] }}</td>
                        <td style="padding: 12px 16px; font-weight: 600;">{{ $rate['target_currency'] }}</td>
                        <td style="padding: 12px 16px;">{{ number_format((float)$rate['rate'], 8) }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="padding: 2px 8px; background: {{ $rate['provider'] === 'manual' ? '#dbeafe' : '#dcfce7' }}; border-radius: 4px; font-size: 12px;">
                                {{ ucfirst($rate['provider']) }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; color: #6b7280;">{{ $rate['effective_at'] }}</td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <button wire:click="editRate('{{ $rate['id'] }}')" style="padding: 4px 10px; background: #e5e7eb; color: #374151; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; margin-right: 4px;">Edit</button>
                            <button wire:click="deleteRate('{{ $rate['id'] }}')" onclick="return confirm('Delete this rate?')" style="padding: 4px 10px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 24px; text-align: center; color: #9ca3af;">No exchange rates configured</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
