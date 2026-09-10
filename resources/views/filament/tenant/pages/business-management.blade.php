@php
    $user = auth()->user();
@endphp

<style>
    .dreams-settings-page {
        --sp-navy: #102a43;
        --sp-ink: #334155;
        --sp-muted: #6b7280;
        --sp-border: #e3e8ed;
        --sp-head-bg: #f8fafc;
        --sp-white: #ffffff;
        --sp-primary: #f97316;
        --sp-primary-soft: #ffedd5;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        color: var(--sp-ink);
    }

    .dreams-settings-page * { box-sizing: border-box; }

    .sp-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 20px;
    }

    .sp-page-title h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--sp-navy);
    }

    .sp-page-title h6 {
        margin: 4px 0 0;
        font-size: 13px;
        font-weight: 400;
        color: var(--sp-muted);
    }

    .sp-card {
        background: var(--sp-white);
        border: 1px solid var(--sp-border);
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }

    .sp-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--sp-border);
    }

    .sp-card-head h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 650;
        color: var(--sp-navy);
    }

    .sp-card-body {
        padding: 20px;
    }

    .sp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .sp-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .sp-field.sp-full { grid-column: 1 / -1; }

    .sp-field label {
        font-size: 13px;
        font-weight: 550;
        color: var(--sp-ink);
    }

    .sp-field label span.sp-req { color: #ef4444; }

    .sp-field input,
    .sp-field select {
        width: 100%;
        height: 40px;
        padding: 0 12px;
        border: 1px solid var(--sp-border);
        border-radius: 7px;
        background: #ffffff;
        color: #243447;
        font-size: 13px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .sp-field input:focus,
    .sp-field select:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }

    .sp-hint {
        margin: 0;
        font-size: 12px;
        color: var(--sp-muted);
    }

    .sp-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--sp-border);
    }

    .sp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 120px;
        height: 40px;
        padding: 0 20px;
        border: 0;
        border-radius: 7px;
        background: var(--sp-primary);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s ease, box-shadow .15s ease;
    }

    .sp-btn:hover {
        background: #ea580c;
        box-shadow: 0 8px 18px rgba(249, 115, 22, .20);
    }

    .sp-btn-danger {
        background: #ef4444;
    }

    .sp-btn-danger:hover {
        background: #dc2626;
        box-shadow: 0 8px 18px rgba(239, 68, 68, .20);
    }

    .sp-btn-secondary {
        background: #ffffff;
        color: var(--sp-ink);
        border: 1px solid var(--sp-border);
    }

    .sp-btn-secondary:hover {
        background: #f9fafb;
    }

    .sp-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .sp-modal {
        background: #fff;
        border-radius: 12px;
        max-width: 440px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        overflow: hidden;
    }

    .sp-modal-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--sp-border);
    }

    .sp-modal-head .sp-modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #fef2f2;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sp-modal-head h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 650;
        color: #991b1b;
    }

    .sp-modal-body {
        padding: 20px 24px;
    }

    .sp-modal-body p {
        margin: 0 0 12px;
        font-size: 13px;
        color: var(--sp-muted);
        line-height: 1.5;
    }

    .sp-modal-body p:last-child { margin-bottom: 0; }

    .sp-modal-body ul {
        margin: 8px 0 0;
        padding-left: 18px;
        font-size: 13px;
        color: #b91c1c;
        line-height: 1.7;
    }

    .sp-modal-foot {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px;
        border-top: 1px solid var(--sp-border);
        background: #fafafa;
    }

    @media (max-width: 720px) {
        .sp-form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dreams-settings-page">
    <div class="sp-page-header">
        <div class="sp-page-title">
            <h4>Business Management</h4>
            <h6>Manage your business account details and preferences</h6>
        </div>
    </div>

    <form class="sp-card" style="max-width: 860px;" wire:submit="save">
        <div class="sp-card-head">
            <h4>Business Information</h4>
        </div>

        <div class="sp-card-body">
            <div class="sp-form-grid">
                <div class="sp-field sp-full">
                    <label for="business_name">Business Name <span class="sp-req">*</span></label>
                    <input id="business_name" type="text" wire:model="business_name" value="{{ $business_name }}" placeholder="Enter business name">
                </div>

                <div class="sp-field">
                    <label for="business_phone">Business Phone</label>
                    <input id="business_phone" type="text" wire:model="business_phone" value="{{ $business_phone }}" placeholder="+256 700 000 000">
                </div>

                <div class="sp-field">
                    <label for="admin_phone">Owner Phone</label>
                    <input id="admin_phone" type="text" wire:model="admin_phone" value="{{ $admin_phone }}" placeholder="+256 700 000 000">
                </div>

                <div class="sp-field sp-full">
                    <label for="business_address">Business Address</label>
                    <input id="business_address" type="text" wire:model="business_address" value="{{ $business_address }}" placeholder="Street, City, Country">
                </div>

                <div class="sp-field">
                    <label for="tin">Tax Identification Number (TIN)</label>
                    <input id="tin" type="text" wire:model="tin" value="{{ $tin }}" placeholder="TIN number">
                </div>

                <div class="sp-field">
                    <label for="currency_code">Currency <span class="sp-req">*</span></label>
                    <select id="currency_code" wire:model="currency_code">
                        <option value="UGX" @selected($currency_code === 'UGX')>UGX - Uganda Shilling</option>
                        <option value="KES" @selected($currency_code === 'KES')>KES - Kenyan Shilling</option>
                        <option value="TZS" @selected($currency_code === 'TZS')>TZS - Tanzanian Shilling</option>
                        <option value="RWF" @selected($currency_code === 'RWF')>RWF - Rwandan Franc</option>
                        <option value="USD" @selected($currency_code === 'USD')>USD - US Dollar</option>
                        <option value="EUR" @selected($currency_code === 'EUR')>EUR - Euro</option>
                        <option value="GBP" @selected($currency_code === 'GBP')>GBP - British Pound</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="sp-card-head" style="border-top: 1px solid var(--sp-border); border-bottom: 0;">
            <h4>Tenant & Preferences</h4>
        </div>

        <div class="sp-card-body">
            <div class="sp-form-grid">
                <div class="sp-field">
                    <label for="admin_email">Account Email</label>
                    <input id="admin_email" type="email" wire:model="admin_email" value="{{ $admin_email }}" placeholder="owner@example.com">
                </div>

                <div class="sp-field">
                    <label for="locale">Language</label>
                    <select id="locale" wire:model="locale">
                        <option value="en" @selected($locale === 'en')>English</option>
                        <option value="fr" @selected($locale === 'fr')>French</option>
                        <option value="sw" @selected($locale === 'sw')>Swahili</option>
                    </select>
                </div>

                <div class="sp-field">
                    <label for="country_code">Country Code</label>
                    <input id="country_code" type="text" wire:model="country_code" value="{{ $country_code }}" placeholder="UG" maxlength="2">
                </div>

                <div class="sp-field">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" wire:model="timezone">
                        <option value="Africa/Kampala" @selected($timezone === 'Africa/Kampala')>Africa/Kampala (EAT, UTC+3)</option>
                        <option value="Africa/Nairobi" @selected($timezone === 'Africa/Nairobi')>Africa/Nairobi (EAT, UTC+3)</option>
                        <option value="Africa/Dar_es_Salaam" @selected($timezone === 'Africa/Dar_es_Salaam')>Africa/Dar_es_Salaam (EAT, UTC+3)</option>
                        <option value="Africa/Kigali" @selected($timezone === 'Africa/Kigali')>Africa/Kigali (CAT, UTC+2)</option>
                        <option value="UTC" @selected($timezone === 'UTC')>UTC</option>
                    </select>
                </div>
            </div>

            <div class="sp-actions">
                <button type="submit" class="sp-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 3v18l7-5 7 5V3a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2Z" />
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>