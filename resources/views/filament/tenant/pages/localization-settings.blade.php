<div style="max-width: 900px; margin: 0 auto; padding: 24px;">

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Localization</h1>
        <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Configure language, currency, timezone, and formatting preferences.</p>
    </div>

    {{-- Language --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Language</h2>
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0;">Select the default interface language for this tenant.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Default Language</label>
                <select wire:model="default_language" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($languages as $lang)
                        <option value="{{ $lang['code'] }}" {{ $default_language === $lang['code'] ? 'selected' : '' }}>
                            {{ $lang['name'] }} ({{ $lang['native_name'] }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Text Direction</label>
                <div style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb;">
                    {{ $preview['direction'] === 'rtl' ? 'Right to Left (RTL)' : 'Left to Right (LTR)' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Currency --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Currency</h2>
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0;">Configure the default currency for transactions.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Default Currency</label>
                <select wire:model="default_currency" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($currencies as $cur)
                        <option value="{{ $cur['code'] }}" {{ $default_currency === $cur['code'] ? 'selected' : '' }}>
                            {{ $cur['name'] }} ({{ $cur['code'] }}) - {{ $cur['symbol'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Decimal Places</label>
                <div style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb;">
                    @php
                        $cur = collect($currencies)->firstWhere('code', $default_currency);
                    @endphp
                    {{ $cur['decimal_places'] ?? 2 }}
                </div>
            </div>
        </div>
    </div>

    {{-- Regional --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Regional Settings</h2>
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0;">Configure timezone, date, and time formatting.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Timezone</label>
                <select wire:model="timezone" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($timezones as $tz => $label)
                        <option value="{{ $tz }}" {{ $timezone === $tz ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Date Format</label>
                <select wire:model="date_format" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    @foreach($dateFormats as $fmt => $label)
                        <option value="{{ $fmt }}" {{ $date_format === $fmt ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">Time Format</label>
                <select wire:model="time_format" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
                    <option value="24" {{ $time_format === '24' ? 'selected' : '' }}>24-hour (14:45)</option>
                    <option value="12" {{ $time_format === '12' ? 'selected' : '' }}>12-hour (2:45 PM)</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #166534; margin: 0 0 16px 0;">Preview</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div>
                <span style="font-size: 12px; color: #15803d; font-weight: 500; text-transform: uppercase;">Date</span>
                <p style="font-size: 20px; font-weight: 600; color: #166534; margin: 4px 0 0 0;">{{ $preview['date'] }}</p>
            </div>
            <div>
                <span style="font-size: 12px; color: #15803d; font-weight: 500; text-transform: uppercase;">Time</span>
                <p style="font-size: 20px; font-weight: 600; color: #166534; margin: 4px 0 0 0;">{{ $preview['time'] }}</p>
            </div>
            <div>
                <span style="font-size: 12px; color: #15803d; font-weight: 500; text-transform: uppercase;">Amount</span>
                <p style="font-size: 20px; font-weight: 600; color: #166534; margin: 4px 0 0 0;">{{ $preview['amount'] }}</p>
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button wire:click="save" style="padding: 10px 24px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
            Save Settings
        </button>
    </div>

</div>
