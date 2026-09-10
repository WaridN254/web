@php
    $lockUser = auth()->user();
    $lockName = $lockUser?->full_name ?? $lockUser?->name ?? 'Admin User';
    $lockEmail = $lockUser?->email ?? '';
    $lockAvatarUrl = $lockUser?->getFilamentAvatarUrl();
    $lockInitials = collect(explode(' ', trim($lockName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'AU';
    $lockIdleMs = (int) (config('app.lock_screen_idle_minutes', 10) * 60 * 1000);
    $lockInitiallyLocked = (bool) session('locked', false);
@endphp

<style>
    #dreams-lock-overlay {
        --lk-navy: #102a43;
        --lk-navy-dark: #0b1f35;
        --lk-orange: #ff9638;
        --lk-orange-hover: #f18422;
        --lk-border: #e3e8ed;
        --lk-muted: #6b7280;
        --lk-red: #ef4444;
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f6f9fc 0%, #eef2f7 100%);
        color: var(--lk-navy);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    #dreams-lock-overlay.is-locked { display: flex; }

    .dreams-lock-bg-deco {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 46%;
        height: 78%;
        background: linear-gradient(160deg, rgba(255, 150, 56, .14), rgba(22, 42, 87, .18));
        border-radius: 60% 0 0 0;
        pointer-events: none;
    }

    .dreams-lock-inner {
        position: relative;
        width: 100%;
        max-width: 480px;
        padding: 20px;
    }

    .dreams-lock-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 26px;
    }

    .dreams-lock-logo svg { color: var(--lk-navy); }

    .dreams-lock-wordmark {
        display: flex;
        flex-direction: column;
        line-height: 1;
    }

    .dreams-lock-pos-label {
        margin-left: 2px;
        color: var(--lk-orange);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .11em;
    }

    .dreams-lock-name-label {
        margin-top: 2px;
        color: var(--lk-navy);
        font-size: 25px;
        font-weight: 850;
        letter-spacing: -0.045em;
    }

    .dreams-lockbox {
        background: #ffffff;
        border: 1px solid var(--lk-border);
        border-radius: 12px;
        padding: 34px 32px 30px;
        box-shadow: 0 22px 50px rgba(15, 23, 42, .12);
    }

    .dreams-lock-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 24px;
    }

    .dreams-lock-subtitle {
        margin: 0 0 14px;
        color: var(--lk-muted);
        font-size: 14px;
        font-weight: 500;
    }

    .dreams-lock-avatar {
        width: 68px;
        height: 68px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--lk-orange);
        color: #ffffff;
        font-size: 22px;
        font-weight: 850;
        margin-bottom: 12px;
    }

    .dreams-lock-user-name {
        margin: 0;
        color: var(--lk-navy);
        font-size: 18px;
        font-weight: 760;
    }

    .dreams-lock-user-email {
        margin: 3px 0 0;
        color: var(--lk-muted);
        font-size: 12px;
    }

    .dreams-lock-pass-group {
        position: relative;
        margin-bottom: 16px;
    }

    .dreams-lock-pass-group input {
        width: 100%;
        height: 44px;
        padding: 0 42px 0 14px;
        border: 1px solid var(--lk-border);
        border-radius: 8px;
        background: #ffffff;
        color: #243447;
        font-size: 14px;
        outline: 0;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .dreams-lock-pass-group input:focus {
        border-color: var(--lk-orange);
        box-shadow: 0 0 0 3px rgba(255, 150, 56, .14);
    }

    .dreams-lock-pass-group input::placeholder { color: #8b95a1; }

    .dreams-lock-eye {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        display: grid;
        place-items: center;
        border: 0;
        background: transparent;
        color: var(--lk-muted);
        cursor: pointer;
    }

    .dreams-lock-eye:hover { color: var(--lk-navy); }

    .dreams-lock-error {
        display: none;
        margin: -6px 0 14px;
        padding: 9px 12px;
        border-radius: 7px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 12px;
        font-weight: 600;
    }

    .dreams-lock-submit {
        width: 100%;
        height: 44px;
        border: 0;
        border-radius: 8px;
        background: var(--lk-orange);
        color: #ffffff;
        font-size: 14px;
        font-weight: 760;
        cursor: pointer;
        transition: background .15s ease, box-shadow .15s ease;
    }

    .dreams-lock-submit:hover {
        background: var(--lk-orange-hover);
        box-shadow: 0 10px 20px rgba(255, 150, 56, .28);
    }

    .dreams-lock-submit:disabled { opacity: .6; cursor: wait; }

    .dreams-lock-foot {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-top: 20px;
    }

    ul.dreams-lock-terms {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px 18px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    ul.dreams-lock-terms li {
        color: var(--lk-muted);
        font-size: 12px;
        cursor: pointer;
    }

    ul.dreams-lock-terms li:hover { color: var(--lk-orange-hover); }

    .dreams-lock-copyright {
        margin: 0;
        color: var(--lk-muted);
        font-size: 12px;
    }
</style>

<div id="dreams-lock-overlay" class="{{ $lockInitiallyLocked ? 'is-locked' : '' }}">
    <div class="dreams-lock-bg-deco" aria-hidden="true"></div>

    <div class="dreams-lock-inner">
        <div class="dreams-lock-logo">
            <svg width="38" height="38" viewBox="0 0 44 44" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 17v-3.5C14 8.3 17.6 5 22 5s8 3.3 8 8.5V17" />
                <path d="M10 17h24l2.2 19.2A3.5 3.5 0 0 1 32.8 40H11.2a3.5 3.5 0 0 1-3.4-3.8L10 17Z" />
                <path d="M17 24h10" />
                <path d="M17 30h7" />
            </svg>
            <span class="dreams-lock-wordmark">
                <span class="dreams-lock-pos-label">POS</span>
                <span class="dreams-lock-name-label">HALIS</span>
            </span>
        </div>

        <div class="dreams-lockbox">
            <div class="dreams-lock-info">
                <p class="dreams-lock-subtitle">Welcome back!</p>
                @if ($lockAvatarUrl)
                    <img src="{{ $lockAvatarUrl }}" alt="{{ $lockName }}" class="dreams-lock-avatar" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                    <span class="dreams-lock-avatar" style="display:none;">{{ $lockInitials }}</span>
                @else
                    <span class="dreams-lock-avatar">{{ $lockInitials }}</span>
                @endif
                <h5 class="dreams-lock-user-name">{{ $lockName }}</h5>
                @if ($lockEmail)
                    <p class="dreams-lock-user-email">{{ $lockEmail }}</p>
                @endif
            </div>

            <div class="dreams-lock-error" id="dreams-lock-error">Incorrect password. Please try again.</div>

            <div class="dreams-lock-pass-group">
                <input type="password" id="dreams-lock-password" class="dreams-lock-pass-input" placeholder="Enter your password" autocomplete="current-password">
                <button type="button" class="dreams-lock-eye" id="dreams-lock-eye" aria-label="Show password" title="Show password">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <button type="button" class="dreams-lock-submit" id="dreams-lock-submit">Log In</button>
        </div>

        <div class="dreams-lock-foot">
            <ul class="dreams-lock-terms">
                <li>Terms &amp; Condition</li>
                <li>Privacy</li>
                <li>Help</li>
                <li>English</li>
            </ul>
            <p class="dreams-lock-copyright">Copyright &copy; 2026 HALIS. All rights reserved</p>
        </div>
    </div>
</div>

<script>
    window.dreamsLockScreen = (function () {
        const overlay = () => document.getElementById('dreams-lock-overlay');
        const passwordInput = () => document.getElementById('dreams-lock-password');
        const errorBox = () => document.getElementById('dreams-lock-error');
        const submitBtn = () => document.getElementById('dreams-lock-submit');
        const idleMs = {{ $lockIdleMs }};
        let idleTimer = null;

        function csrfHeaders() {
            const headers = {};
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) headers['X-CSRF-TOKEN'] = meta.content;
            const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            if (m) headers['X-XSRF-TOKEN'] = decodeURIComponent(m[1]);
            return headers;
        }

        function call(path, body) {
            return fetch(path, {
                method: 'POST',
                headers: Object.assign({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }, csrfHeaders()),
                body: body ? JSON.stringify(body) : undefined,
            });
        }

        function syncLockState() {
            fetch('/tenant/lock-status', { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    const el = overlay();
                    if (!el) return;
                    if (data && data.locked) {
                        el.classList.add('is-locked');
                        setTimeout(() => passwordInput()?.focus(), 80);
                    } else {
                        el.classList.remove('is-locked');
                    }
                })
                .catch(function () {});
        }

        function lock() {
            call('/tenant/lock').catch(() => {});
            overlay().classList.add('is-locked');
            setTimeout(() => passwordInput()?.focus(), 80);
        }

        async function unlock() {
            const input = passwordInput();
            const btn = submitBtn();
            const err = errorBox();
            if (!input || !btn) return;
            if (err) err.style.display = 'none';
            if (!input.value) {
                if (err) { err.textContent = 'Please enter your password.'; err.style.display = 'block'; }
                input.focus();
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Unlocking...';
            try {
                const res = await call('/tenant/unlock', { password: input.value });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    overlay().classList.remove('is-locked');
                    input.value = '';
                } else {
                    if (err) { err.textContent = data.message || 'Incorrect password. Please try again.'; err.style.display = 'block'; }
                    input.focus();
                }
            } catch (e) {
                if (err) { err.textContent = 'Unable to unlock. Please try again.'; err.style.display = 'block'; }
            } finally {
                btn.disabled = false;
                btn.textContent = 'Log In';
            }
        }

        function resetIdle() {
            clearTimeout(idleTimer);
            if (idleMs > 0) {
                idleTimer = setTimeout(() => {
                    if (!overlay()?.classList.contains('is-locked')) lock();
                }, idleMs);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const btn = submitBtn();
            const input = passwordInput();
            const eye = document.getElementById('dreams-lock-eye');
            if (btn) btn.addEventListener('click', unlock);
            if (input) {
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') unlock();
                });
            }
            if (eye) {
                eye.addEventListener('click', function () {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    eye.innerHTML = isPassword
                        ? '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>'
                        : '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
                    eye.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                });
            }
            if (overlay()?.classList.contains('is-locked')) {
                setTimeout(() => input?.focus(), 80);
            }
            ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'].forEach(function (ev) {
                document.addEventListener(ev, resetIdle, { passive: true });
            });
            resetIdle();
            syncLockState();
        });

        window.addEventListener('pageshow', function (e) {
            if (e.persisted) syncLockState();
        });

        return { lock: lock, unlock: unlock };
    })();
</script>