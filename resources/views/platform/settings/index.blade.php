@extends('platform.layout')
@section('title', 'Platform Settings')

@section('content')
<form method="POST" action="{{ route('platform.settings.update') }}" id="settings-form">
    @csrf
    @method('PUT')

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
        <div>
            <h2 style="font-size:1.3rem;font-weight:800;color:#1a1a2e">Platform Settings</h2>
            <p style="font-size:.85rem;color:#64748b;margin-top:2px">Configure your platform, email delivery, and subscriptions</p>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 24px">Save Changes</button>
    </div>

    <div x-data="{ activeTab: '{{ $settings->has('general') ? 'general' : 'email' }}' }" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">

        {{-- Tab Bar --}}
        <div style="display:flex;border-bottom:1px solid #e2e8f0;padding:0 8px;background:#f8fafc">
            <button type="button" @click="activeTab = 'email'" style="padding:14px 20px;font-size:.88rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;font-family:inherit;white-space:nowrap" :style="activeTab === 'email' ? 'color:#6366f1;border-bottom-color:#6366f1;background:#fff' : 'color:#64748b'">
                <span style="margin-right:6px">📧</span> Email / SMTP
            </button>
            @if($settings->has('general'))
            <button type="button" @click="activeTab = 'general'" style="padding:14px 20px;font-size:.88rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;font-family:inherit;white-space:nowrap" :style="activeTab === 'general' ? 'color:#6366f1;border-bottom-color:#6366f1;background:#fff' : 'color:#64748b'">
                <span style="margin-right:6px">⚙️</span> General
            </button>
            @endif
            @if($settings->has('subscription'))
            <button type="button" @click="activeTab = 'subscription'" style="padding:14px 20px;font-size:.88rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;font-family:inherit;white-space:nowrap" :style="activeTab === 'subscription' ? 'color:#6366f1;border-bottom-color:#6366f1;background:#fff' : 'color:#64748b'">
                <span style="margin-right:6px">💳</span> Subscription
            </button>
            @endif
        </div>

        <div style="padding:28px">

            {{-- EMAIL TAB --}}
            <div x-show="activeTab === 'email'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

                {{-- Status Cards --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px">
                    <div style="background:{{ $mailerStatus === 'production' ? '#f0fdf4' : '#fffbeb' }};border:1px solid {{ $mailerStatus === 'production' ? '#bbf7d0' : '#fde68a' }};border-radius:10px;padding:16px 20px">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:{{ $mailerStatus === 'production' ? '#166534' : '#92400e' }};margin-bottom:6px">Current Mailer</div>
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <div style="font-size:1.1rem;font-weight:800;color:{{ $mailerStatus === 'production' ? '#166534' : '#92400e' }}">{{ ucfirst($currentMailer) }}</div>
                            <span class="badge {{ $mailerStatus === 'production' ? 'badge-success' : 'badge-warning' }}">{{ $mailerStatus === 'production' ? 'Live' : 'Dev' }}</span>
                        </div>
                        <div style="font-size:.78rem;color:{{ $mailerStatus === 'production' ? '#15803d' : '#a16207' }};margin-top:4px">
                            @if($currentMailer === 'log')
                                Emails logged to file — no real emails sent
                            @else
                                Sending via {{ ucfirst($currentMailer) }} transport
                            @endif
                        </div>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:6px">Queue Driver</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#1a1a2e">{{ ucfirst($queueDriver) }}</div>
                        <div style="font-size:.78rem;color:#64748b;margin-top:4px">
                            @if($queueDriver === 'sync')
                                Synchronous — no worker needed
                            @else
                                Requires <code style="background:#e0e7ff;padding:1px 5px;border-radius:3px;font-size:.75rem">queue:work</code>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- SMTP Fields --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
                    @foreach($emailSettings as $setting)
                    <div class="form-group">
                        <label class="form-label" style="font-size:.82rem">
                            @if($setting->key === 'mail_mailer') Delivery Method
                            @elseif($setting->key === 'mail_api_key') API Key (Resend)
                            @elseif($setting->key === 'mail_host') SMTP Host
                            @elseif($setting->key === 'mail_port') SMTP Port
                            @elseif($setting->key === 'mail_username') Username
                            @elseif($setting->key === 'mail_password') Password / App Password
                            @elseif($setting->key === 'mail_encryption') Encryption
                            @elseif($setting->key === 'mail_from_address') From Address
                            @elseif($setting->key === 'mail_from_name') From Name
                            @elseif($setting->key === 'mail_reply_to_address') Reply-To Address
                            @else {{ str_replace('_', ' ', ucfirst($setting->key)) }}
                            @endif
                        </label>

                        @if($setting->type === 'select' && $setting->options)
                            @php $opts = is_string($setting->options) ? json_decode($setting->options, true) : $setting->options; @endphp
                            <select name="settings[{{ $setting->key }}]" class="form-select">
                                @foreach($opts as $optValue => $optLabel)
                                    <option value="{{ $optValue }}" {{ $setting->value === $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($setting->type === 'password')
                            <input class="form-input" type="password" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" placeholder="Leave blank to keep current" autocomplete="new-password">
                            @if($setting->value)
                                <div style="display:flex;align-items:center;gap:4px;margin-top:5px;font-size:.73rem;color:#16a34a;font-weight:500">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                                    Password is set
                                </div>
                            @endif
                        @else
                            <input class="form-input" type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                placeholder="@if($setting->key === 'mail_host') smtp.gmail.com @elseif($setting->key === 'mail_port') 587 @elseif($setting->key === 'mail_username') your-email@gmail.com @elseif($setting->key === 'mail_from_address') no-reply@halis.com @elseif($setting->key === 'mail_from_name') HALIS @elseif($setting->key === 'mail_reply_to_address') support@halis.com @endif">
                        @endif

                        @if($setting->key === 'mail_api_key')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px">
                                Get your key at <a href="https://resend.com/api-keys" target="_blank" style="color:#6366f1;text-decoration:underline">resend.com/api-keys</a>. Free tier: 100 emails/day.
                                For testing, Resend provides <strong>onboarding@resend.dev</strong> as default sender.
                            </div>
                        @endif
                        @if($setting->key === 'mail_mailer')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px"><strong>Resend API</strong> = HTTPS (recommended) · <strong>SMTP</strong> = traditional · <strong>Log</strong> = dev only</div>
                        @endif
                        @if($setting->key === 'mail_host')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px"><strong>Gmail:</strong> smtp.gmail.com · <strong>Outlook:</strong> smtp-mail.outlook.com</div>
                        @endif
                        @if($setting->key === 'mail_port')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px"><strong>587</strong> = TLS · <strong>465</strong> = SSL</div>
                        @endif
                        @if($setting->key === 'mail_password')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px">
                                <strong>Gmail:</strong> Use an <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#6366f1;text-decoration:underline">App Password</a> (requires 2FA)
                            </div>
                        @endif
                        @if($setting->key === 'mail_encryption')
                            <div style="font-size:.73rem;color:#64748b;margin-top:5px">Most providers use <strong>TLS</strong> on port 587</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Queue Restart --}}
                <div style="margin-top:24px;padding:14px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:1rem">🔒</span>
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#374151">Queue Worker</div>
                            <div style="font-size:.75rem;color:#64748b">Restart workers after saving SMTP changes</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('platform.settings.restart-queue') }}" onsubmit="return confirm('Restart queue workers?')">
                        @csrf
                        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:inherit">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23,4 23,10 17,10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                            Restart Queue
                        </button>
                    </form>
                </div>
            </div>

            {{-- GENERAL TAB --}}
            @if($settings->has('general'))
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
                    @foreach($settings['general'] as $setting)
                    <div class="form-group">
                        <label class="form-label" style="font-size:.82rem">{{ str_replace('_', ' ', ucfirst($setting->key)) }}</label>
                        @if($setting->type === 'boolean')
                            <label class="form-check" style="padding:10px 0">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                <span style="font-size:.88rem;color:#374151">Enabled</span>
                            </label>
                        @elseif($setting->type === 'json')
                            <textarea class="form-input" name="settings[{{ $setting->key }}]" rows="3">{{ $setting->value }}</textarea>
                        @else
                            <input class="form-input" type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- SUBSCRIPTION TAB --}}
            @if($settings->has('subscription'))
            <div x-show="activeTab === 'subscription'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
                    @foreach($settings['subscription'] as $setting)
                    <div class="form-group">
                        <label class="form-label" style="font-size:.82rem">{{ str_replace('_', ' ', ucfirst($setting->key)) }}</label>
                        @if($setting->type === 'boolean')
                            <label class="form-check" style="padding:10px 0">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                <span style="font-size:.88rem;color:#374151">Enabled</span>
                            </label>
                        @elseif($setting->type === 'json')
                            <textarea class="form-input" name="settings[{{ $setting->key }}]" rows="3">{{ $setting->value }}</textarea>
                        @else
                            <input class="form-input" type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Sticky Save --}}
    <div style="position:sticky;bottom:0;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border:1px solid #e2e8f0;border-radius:12px;padding:14px 24px;display:flex;justify-content:flex-end;gap:10px;margin-top:20px;box-shadow:0 -4px 12px rgba(0,0,0,.05)">
        <a href="{{ route('platform.dashboard') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding:10px 28px">Save All Settings</button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
