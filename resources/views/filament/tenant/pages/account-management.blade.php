@php
    $user = auth()->user();
    $userName = $user?->full_name ?? $user?->name ?? 'Admin User';
    $userEmail = $user?->email ?? '';
    $avatarUrl = $user?->getFilamentAvatarUrl();
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'AU';
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

    .sp-field input {
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

    .sp-field input:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }

    .sp-hint {
        margin: 0;
        font-size: 12px;
        color: var(--sp-muted);
    }

    .sp-avatar-row {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sp-avatar-fallback {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        flex: 0 0 56px;
        border-radius: 12px;
        background: var(--sp-primary);
        color: #ffffff;
        font-size: 19px;
        font-weight: 800;
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

    @media (max-width: 720px) {
        .sp-form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dreams-settings-page">
    <div class="sp-page-header">
        <div class="sp-page-title">
            <h4>Account Management</h4>
            <h6>Manage your personal account details and password</h6>
        </div>
    </div>

    @if (session('avatar_status'))
        <div style="max-width:860px; margin:0 0 16px; padding:12px 16px; border-radius:8px; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; font-size:13px; font-weight:600;">
            {{ session('avatar_status') }}
        </div>
    @endif

    <div class="sp-card" style="max-width: 860px;">
        <div class="sp-card-head">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $userName }}" style="width:34px; height:34px; flex:0 0 34px; border-radius:9px; object-fit:cover;">
            @else
                <span class="sp-avatar-fallback" style="width:34px; height:34px; flex-basis:34px; font-size:13px;">{{ $initials }}</span>
            @endif
            <div>
                <h4>{{ $userName }}</h4>
                <p class="sp-hint" style="margin-top:2px;">{{ $userEmail }}</p>
            </div>
        </div>

        <div class="sp-card-body" style="border-bottom: 1px solid var(--sp-border);">
            <form method="POST" action="/tenant/user-avatars/upload" enctype="multipart/form-data" id="avatar-upload-form" style="margin:0;">
                @csrf
                <div class="sp-avatar-row">
                    <div style="position:relative; width:100px; height:100px; flex:0 0 100px;">
                        <img id="avatar-photo-img" src="{{ $avatarUrl }}" alt="Profile photo" style="width:100px; height:100px; border-radius:50%; object-fit:cover; display:{{ $avatarUrl ? 'block' : 'none' }};">
                        <div id="avatar-photo-fallback" class="sp-avatar-fallback" style="width:100px; height:100px; flex-basis:100px; font-size:30px; border-radius:50%; display:{{ $avatarUrl ? 'none' : 'grid' }};">{{ $initials }}</div>
                        @if ($avatarUrl)
                            <button type="submit" form="avatar-remove-form" title="Remove photo" style="position:absolute; top:0; right:0; width:24px; height:24px; display:grid; place-items:center; border:0; border-radius:50%; background:#ef4444; color:#fff; font-size:14px; line-height:1; cursor:pointer;">&times;</button>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <p style="margin:0 0 4px; font-size:14px; font-weight:650; color:var(--sp-navy);">Profile Photo</p>
                        <p class="sp-hint">Upload an image below 2 MB. Accepted file format JPG, PNG</p>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:12px; flex-wrap:wrap;">
                            <label for="profile_image_input" class="sp-btn" style="cursor:pointer; min-width:0; padding:0 16px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 16V4" /><path d="m6 10 6-6 6 6" /><path d="M4 20h16" />
                                </svg>
                                Change Image
                            </label>
                            <input id="profile_image_input" type="file" name="avatar" accept="image/png,image/jpeg" required style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;" onchange="previewAvatar(event)">
                            <button type="submit" class="sp-btn" id="avatar-save-btn" style="display:none; background:#0E9384;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                Save Photo
                            </button>
                            <button type="button" class="sp-btn" id="avatar-cancel-btn" style="display:none; background:#6b7280;" onclick="resetAvatar()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                                </svg>
                                Cancel
                            </button>
                        </div>
                        <p class="sp-hint" style="margin-top:8px;">Max 2 MB. The new photo is shown in the top bar and profile menu.</p>
                        @error('avatar')
                            <p class="sp-hint" style="color:#ef4444; margin-top:8px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </form>

            @if ($avatarUrl)
                <form id="avatar-remove-form" method="POST" action="/tenant/user-avatars/remove" style="margin:0;">
                    @csrf
                </form>
            @endif

            <script>
                function previewAvatar(event) {
                    var input = event.target;
                    var img = document.getElementById('avatar-photo-img');
                    var fb = document.getElementById('avatar-photo-fallback');
                    var saveBtn = document.getElementById('avatar-save-btn');
                    var cancelBtn = document.getElementById('avatar-cancel-btn');
                    if (input.files && input.files[0]) {
                        img.src = URL.createObjectURL(input.files[0]);
                        img.style.display = 'block';
                        if (fb) fb.style.display = 'none';
                        if (saveBtn) saveBtn.style.display = 'inline-flex';
                        if (cancelBtn) cancelBtn.style.display = 'inline-flex';
                    }
                }

                function resetAvatar() {
                    var input = document.getElementById('profile_image_input');
                    var img = document.getElementById('avatar-photo-img');
                    var fb = document.getElementById('avatar-photo-fallback');
                    var saveBtn = document.getElementById('avatar-save-btn');
                    var cancelBtn = document.getElementById('avatar-cancel-btn');
                    input.value = '';
                    if (fb) { fb.style.display = 'grid'; img.style.display = 'none'; img.removeAttribute('src'); }
                    if (saveBtn) saveBtn.style.display = 'none';
                    if (cancelBtn) cancelBtn.style.display = 'none';
                }
            </script>
        </div>

        <form class="sp-card-body" wire:submit="save">
            <div class="sp-form-grid">
                <div class="sp-field sp-full">
                    <label for="full_name">Full Name <span class="sp-req">*</span></label>
                    <input id="full_name" type="text" wire:model="full_name" value="{{ $full_name }}" placeholder="Enter your full name">
                </div>

                <div class="sp-field">
                    <label for="email">Email Address <span class="sp-req">*</span></label>
                    <input id="email" type="email" wire:model="email" value="{{ $email }}" placeholder="you@example.com">
                </div>

                <div class="sp-field">
                    <label for="phone">Phone Number</label>
                    <input id="phone" type="text" wire:model="phone" value="{{ $phone }}" placeholder="+256 700 000 000">
                </div>
            </div>

            <div class="sp-form-grid" style="margin-top: 18px;">
                <div class="sp-field">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" type="password" wire:model="current_password" placeholder="Required to change password" autocomplete="current-password">
                </div>

                <div class="sp-field">
                    <label for="new_password">New Password</label>
                    <input id="new_password" type="password" wire:model="new_password" placeholder="Min 8 characters" autocomplete="new-password">
                </div>

                <div class="sp-field">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input id="new_password_confirmation" type="password" wire:model="new_password_confirmation" placeholder="Repeat new password" autocomplete="new-password">
                </div>
            </div>

            <p class="sp-hint" style="margin-top: 14px;">Leave the password fields empty to keep your current password.</p>

            <div class="sp-actions">
                <button type="submit" class="sp-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 3v18l7-5 7 5V3a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2Z" />
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>