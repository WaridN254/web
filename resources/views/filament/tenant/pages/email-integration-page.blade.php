<x-filament-panels::page>
<div style="max-width:960px;margin:0 auto;">

    {{-- HEADER --}}
    <div style="margin-bottom:28px;">
        <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0 0 6px;">Email Integration</h1>
        <p style="font-size:14px;color:#64748b;margin:0;">Configure your email provider to send and receive emails from the POS system.</p>
    </div>

    {{-- EXISTING ACCOUNTS --}}
    @if(count($existingAccounts) > 0)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px;">Connected Accounts</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($existingAccounts as $acc)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border:1px solid #e5e7eb;border-radius:10px;background:{{ $acc['is_active'] ? '#f8fafc' : '#fef2f2' }};">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:{{ $acc['connection_status'] === 'connected' ? '#dcfce7' : '#fee2e2' }};display:flex;align-items:center;justify-content:center;">
                        <svg style="width:20px;height:20px;color:{{ $acc['connection_status'] === 'connected' ? '#16a34a' : '#dc2626' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $acc['connection_status'] === 'connected' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}}"></path>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#0f172a;">{{ $acc['display_name'] }}</div>
                        <div style="font-size:12px;color:#64748b;">{{ $acc['email_address'] }} &middot; {{ ucfirst($acc['provider']) }} &middot; {{ ucfirst($acc['connection_status']) }}</div>
                    </div>
                </div>
                <div style="display:flex;gap:6px;">
                    <button wire:click="editExistingAccount('{{ $acc['id'] }}')" style="padding:6px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:12px;font-weight:500;cursor:pointer;color:#374151;">Edit</button>
                    <button wire:click="deleteExistingAccount('{{ $acc['id'] }}')" onclick="return confirm('Remove this account?')" style="padding:6px 12px;border:1px solid #fecaca;border-radius:6px;background:#fff;font-size:12px;font-weight:500;cursor:pointer;color:#dc2626;">Remove</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- PROVIDER SELECTION --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 4px;">1. Select Email Provider</h2>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Choose your email provider to auto-fill server settings.</p>

        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;">
            @foreach(self::$providers as $key => $provider)
            <button wire:click="selectProvider('{{ $key }}')"
                style="padding:16px 12px;border:2px solid {{ $selectedProvider === $key ? $provider['color'] : '#e5e7eb' }};border-radius:10px;background:{{ $selectedProvider === $key ? $provider['color'] . '08' : '#fff' }};cursor:pointer;text-align:center;transition:all 0.15s;">
                <div style="width:44px;height:44px;border-radius:10px;background:{{ $provider['color'] }}15;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                    <svg style="width:22px;height:22px;color:{{ $provider['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($key === 'gmail')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        @elseif($key === 'outlook')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        @elseif($key === 'yahoo')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        @elseif($key === 'zoho')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        @endif
                    </svg>
                </div>
                <div style="font-size:13px;font-weight:600;color:{{ $selectedProvider === $key ? $provider['color'] : '#374151' }};">{{ $provider['name'] }}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $provider['description'] }}</div>
            </button>
            @endforeach
        </div>

        {{-- Provider Instructions --}}
        @php $preset = self::$providers[$selectedProvider]; @endphp
        @if($preset['instructions'])
        <div style="margin-top:16px;padding:12px 16px;border-radius:8px;background:{{ $preset['color'] }}08;border:1px solid {{ $preset['color'] }}20;">
            <div style="display:flex;gap:8px;align-items:flex-start;">
                <svg style="width:18px;height:18px;color:{{ $preset['color'] }};flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div style="font-size:13px;color:#374151;line-height:1.5;">{{ $preset['instructions'] }}</div>
            </div>
        </div>
        @endif
    </div>

    {{-- ACCOUNT CONFIGURATION --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 4px;">{{ $editingAccountId ? '2. Edit Account' : '2. Account Details' }}</h2>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Enter your email account credentials.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Display Name *</label>
                <input type="text" wire:model.live="accountDisplayName" value="{{ $accountDisplayName }}" placeholder="e.g. Nova Retail Support" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Email Address *</label>
                <input type="email" wire:model.live="accountEmail" value="{{ $accountEmail }}" placeholder="e.g. support@novaretail.com" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
        </div>
    </div>

    {{-- SMTP SETTINGS --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                <svg style="width:18px;height:18px;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </div>
            <div>
                <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">SMTP Settings (Sending)</h2>
                <p style="font-size:12px;color:#64748b;margin:0;">Server for sending outgoing emails</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">SMTP Host *</label>
                <input type="text" wire:model.live="smtpHost" value="{{ $smtpHost }}" placeholder="smtp.gmail.com" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Port</label>
                <input type="number" wire:model.live="smtpPort" value="{{ $smtpPort }}" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Encryption</label>
                <select wire:model.live="smtpEncryption" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;background:#fff;box-sizing:border-box;">
                    <option value="tls" {{ $smtpEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ $smtpEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ $smtpEncryption === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">SMTP Username</label>
                <input type="text" wire:model.live="smtpUsername" value="{{ $smtpUsername }}" placeholder="Usually your email address" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Leave blank to use email address above</div>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">SMTP Password *</label>
                <input type="password" wire:model.live="smtpPassword" placeholder="{{ $editingAccountId ? 'Leave blank to keep current' : 'App password or email password' }}" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                @if($editingAccountId && $smtpPasswordSet)
                <div style="font-size:11px;color:#16a34a;margin-top:3px;display:flex;align-items:center;gap:4px;">
                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Password saved — leave blank to keep current
                </div>
                @endif
                @if($selectedProvider === 'gmail')
                <div style="font-size:11px;color:#dc2626;margin-top:3px;">⚠ Use Gmail App Password, not your regular password. Enable 2FA first.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- IMAP SETTINGS --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:18px;height:18px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <div>
                    <h2 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">IMAP Settings (Receiving)</h2>
                    <p style="font-size:12px;color:#64748b;margin:0;">Server for receiving and syncing incoming emails</p>
                </div>
            </div>
            <button wire:click="toggleImap" style="padding:6px 14px;border:1px solid {{ $imapEnabled ? '#bbf7d0' : '#e5e7eb' }};border-radius:6px;background:{{ $imapEnabled ? '#dcfce7' : '#f9fafb' }};font-size:12px;font-weight:600;cursor:pointer;color:{{ $imapEnabled ? '#166534' : '#6b7280' }};">
                {{ $imapEnabled ? 'Enabled' : 'Disabled' }}
            </button>
        </div>

        @if($imapEnabled)
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">IMAP Host *</label>
                <input type="text" wire:model.live="imapHost" value="{{ $imapHost }}" placeholder="imap.gmail.com" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Port</label>
                <input type="number" wire:model.live="imapPort" value="{{ $imapPort }}" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Encryption</label>
                <select wire:model.live="imapEncryption" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;background:#fff;box-sizing:border-box;">
                    <option value="ssl" {{ $imapEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="tls" {{ $imapEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="none" {{ $imapEncryption === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">IMAP Username</label>
                <input type="text" wire:model.live="imapUsername" value="{{ $imapUsername }}" placeholder="Usually your email address" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">IMAP Password</label>
                <input type="password" wire:model.live="imapPassword" placeholder="{{ $editingAccountId ? 'Leave blank to keep current' : 'Same as SMTP password usually' }}" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                @if($editingAccountId && $imapPasswordSet)
                <div style="font-size:11px;color:#16a34a;margin-top:3px;display:flex;align-items:center;gap:4px;">
                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Password saved — leave blank to keep current
                </div>
                @endif
            </div>
        </div>
        @else
        <div style="padding:16px;background:#f8fafc;border-radius:8px;text-align:center;">
            <p style="font-size:13px;color:#64748b;margin:0;">IMAP is disabled. Emails will only be sent, not received/synced.</p>
        </div>
        @endif
    </div>

    {{-- ACTION BUTTONS --}}
    <div style="display:flex;gap:12px;margin-bottom:24px;">
        <button wire:click="testConnection" {{ $testing ? 'disabled' : '' }} style="padding:10px 24px;border:2px solid #2563eb;border-radius:8px;background:{{ $testing ? '#93c5fd' : '#fff' }};font-size:14px;font-weight:600;cursor:{{ $testing ? 'wait' : 'pointer' }};color:#2563eb;display:flex;align-items:center;gap:8px;">
            @if($testing)
                <svg style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75;"></path></svg>
                Testing...
            @else
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Test Connection
            @endif
        </button>

        <button wire:click="saveAccount" style="padding:10px 24px;border:none;border-radius:8px;background:#16a34a;color:#fff;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ $editingAccountId ? 'Update Account' : 'Save & Connect' }}
        </button>

        @if($editingAccountId)
        <button wire:click="resetForm" style="padding:10px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:14px;font-weight:500;cursor:pointer;color:#64748b;">Cancel Edit</button>
        @endif
    </div>

    {{-- TEST RESULT --}}
    @if($testResult)
    <div style="padding:14px 18px;border-radius:10px;margin-bottom:16px;background:{{ $testSuccess ? '#f0fdf4' : '#fef2f2' }};border:1px solid {{ $testSuccess ? '#bbf7d0' : '#fecaca' }};">
        <div style="font-size:13px;white-space:pre-line;color:{{ $testSuccess ? '#166534' : '#991b1b' }};">{{ $testResult }}</div>
    </div>
    @endif

    {{-- SAVE RESULT --}}
    @if($saveResult)
    <div style="padding:14px 18px;border-radius:10px;margin-bottom:16px;background:{{ $saveSuccess ? '#f0fdf4' : '#fef2f2' }};border:1px solid {{ $saveSuccess ? '#bbf7d0' : '#fecaca' }};">
        <div style="font-size:13px;color:{{ $saveSuccess ? '#166534' : '#991b1b' }};">{{ $saveResult }}</div>
    </div>
    @endif

    {{-- QUICK REFERENCE --}}
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
        <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 12px;">Quick Reference: Server Settings</h3>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:8px 12px;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Provider</th>
                    <th style="padding:8px 12px;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">SMTP Host</th>
                    <th style="padding:8px 12px;text-align:center;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">SMTP Port</th>
                    <th style="padding:8px 12px;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">IMAP Host</th>
                    <th style="padding:8px 12px;text-align:center;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">IMAP Port</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 12px;font-weight:500;color:#0f172a;">Gmail</td>
                    <td style="padding:8px 12px;color:#64748b;">smtp.gmail.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">587</td>
                    <td style="padding:8px 12px;color:#64748b;">imap.gmail.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">993</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 12px;font-weight:500;color:#0f172a;">Outlook</td>
                    <td style="padding:8px 12px;color:#64748b;">smtp.office365.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">587</td>
                    <td style="padding:8px 12px;color:#64748b;">outlook.office365.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">993</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 12px;font-weight:500;color:#0f172a;">Yahoo</td>
                    <td style="padding:8px 12px;color:#64748b;">smtp.mail.yahoo.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">587</td>
                    <td style="padding:8px 12px;color:#64748b;">imap.mail.yahoo.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">993</td>
                </tr>
                <tr>
                    <td style="padding:8px 12px;font-weight:500;color:#0f172a;">Zoho</td>
                    <td style="padding:8px 12px;color:#64748b;">smtp.zoho.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">587</td>
                    <td style="padding:8px 12px;color:#64748b;">imap.zoho.com</td>
                    <td style="padding:8px 12px;text-align:center;color:#64748b;">993</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<style>
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
</style>
</x-filament-panels::page>
