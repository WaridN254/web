<div style="max-width: 600px; margin: 0 auto; padding: 24px;">

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">My Preferences</h1>
        <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Customize your personal interface settings.</p>
    </div>

    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Language</h2>
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0;">Choose your preferred interface language.</p>

        <select wire:model="language" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
            @foreach($languages as $lang)
                <option value="{{ $lang['code'] }}" {{ $language === $lang['code'] ? 'selected' : '' }}>
                    {{ $lang['name'] }} ({{ $lang['native_name'] }})
                </option>
            @endforeach
        </select>
    </div>

    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Timezone</h2>
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0;">Select your preferred timezone for date and time display.</p>

        <select wire:model="timezone" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #fff;">
            @foreach($timezones as $tz => $label)
                <option value="{{ $tz }}" {{ $timezone === $tz ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button wire:click="save" style="padding: 10px 24px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
            Save Preferences
        </button>
    </div>

</div>
