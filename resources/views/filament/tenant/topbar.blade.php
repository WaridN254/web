@php
    $user = auth()->user();
    $userName = $user?->full_name ?? $user?->name ?? 'Admin User';
    $userEmail = $user?->email ?? 'admin@example.com';
    $avatarUrl = $user?->getFilamentAvatarUrl();
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'AU';

    $quickActions = [
        ['label' => __('common.category'), 'url' => '/tenant/categories/create', 'icon' => 'category'],
        ['label' => __('common.product'), 'url' => '/tenant/products/create', 'icon' => 'package'],
        ['label' => __('common.purchase'), 'url' => '/tenant/purchase-orders/create', 'icon' => 'bag'],
        ['label' => __('common.sale'), 'url' => '/tenant/sales-terminal', 'icon' => 'cart'],
        ['label' => __('common.expense'), 'url' => '/tenant/expenses', 'icon' => 'file'],
        ['label' => __('common.quotation'), 'url' => '/tenant/quotation-page', 'icon' => 'receipt'],
        ['label' => __('common.return'), 'url' => '/tenant/sales-return-page', 'icon' => 'return'],
        ['label' => __('common.user'), 'url' => '/tenant/users/create', 'icon' => 'user'],
        ['label' => __('common.customer'), 'url' => '/tenant/customers/create', 'icon' => 'users'],
        ['label' => __('common.biller'), 'url' => '#', 'icon' => 'shield'],
        ['label' => __('common.supplier'), 'url' => '/tenant/suppliers/create', 'icon' => 'supplier'],
        ['label' => __('common.transfer'), 'url' => '#', 'icon' => 'truck'],
    ];
@endphp

<style>
    .fi-topbar-ctn {
        overflow: visible !important;
        z-index: 30 !important;
    }

    /* Force toast notification containers to appear fully visible below the 70px topbar header */
    .fi-no,
    .fi-notifications,
    .fi-no-notification,
    [data-gooey-viewport],
    [wire\:id]:has(.fi-no) {
        position: fixed !important;
        top: 82px !important;
        right: 20px !important;
        z-index: 999999 !important;
    }

    .fi-topbar {
        min-height: 70px !important;
        height: 70px !important;
        overflow: visible !important;
        padding: 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
        background: #ffffff !important;
        box-shadow: none !important;
        ring: 0 !important;
    }

    .fi-topbar > .fi-topbar-end,
    .fi-topbar-end,
    .fi-topbar > .fi-topbar-open-sidebar-btn,
    .fi-topbar > .fi-topbar-close-sidebar-btn,
    .fi-topbar-collapse-sidebar-btn-ctn,
    .fi-topbar-open-collapse-sidebar-btn,
    .fi-topbar-close-collapse-sidebar-btn,
    .fi-topbar-start > .fi-icon-btn,
    .fi-topbar-start > button {
        display: none !important;
    }

    .fi-topbar .fi-logo {
        display: none !important;
    }

    .dreams-pos-topbar,
    .dreams-pos-topbar * {
        box-sizing: border-box;
    }

    .dreams-pos-topbar {
        --pos-navy: #102a43;
        --pos-navy-dark: #0b1f35;
        --pos-orange: #ff9638;
        --pos-orange-hover: #f18422;
        --pos-border: #e3e8ed;
        --pos-muted: #6b7280;
        --pos-control: #f5f7f9;
        width: 100%;
        height: 70px;
        display: flex;
        align-items: center;
        gap: 0;
        background: #ffffff;
        color: var(--pos-navy);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        position: relative;
        z-index: 30;
    }

    .dreams-brand {
        display: flex;
        align-items: center;
        height: 100%;
        width: 16rem;
        flex: 0 0 16rem;
        padding: 0 20px;
        border-right: 1px solid var(--pos-border);
        box-sizing: border-box;
        transition: width .2s ease, flex-basis .2s ease;
    }

    .dreams-brand.is-collapsed {
        width: 5rem;
        flex: 0 0 5rem;
        padding: 0 12px;
        justify-content: center;
    }

    .dreams-brand.is-collapsed .pos-brand-text {
        display: none !important;
    }

    .dreams-brand-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: var(--pos-navy);
    }

    .dreams-logo-mark {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        color: var(--pos-navy);
    }

    .dreams-wordmark {
        display: flex;
        flex-direction: column;
        line-height: 1;
    }

    .dreams-pos-label {
        margin-left: 2px;
        color: var(--pos-orange);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .11em;
    }

    .dreams-name {
        margin-top: 2px;
        color: var(--pos-navy);
        font-size: 25px;
        font-weight: 850;
        letter-spacing: -0.045em;
    }

    .dreams-main-row {
        min-width: 0;
        flex: 1 1 auto;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 24px 0 0;
    }

    .dreams-collapse-btn {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        flex: 0 0 28px;
        margin-left: -14px;
        border: 0;
        border-radius: 999px;
        background: var(--pos-orange);
        color: #ffffff;
        cursor: pointer;
        transition: background .18s ease, transform .18s ease;
    }

    .dreams-collapse-btn:hover {
        background: var(--pos-orange-hover);
        transform: translateY(-1px);
    }

    .dreams-search {
        width: 250px;
        height: 36px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 250px;
        margin-left: 10px;
        padding: 0 9px;
        border: 1px solid var(--pos-border);
        border-radius: 8px;
        background: #ffffff;
        color: var(--pos-muted);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .dreams-search:focus-within {
        border-color: var(--pos-orange);
        box-shadow: 0 0 0 3px rgba(255, 150, 56, .12);
    }

    .dreams-search input {
        min-width: 0;
        flex: 1 1 auto;
        border: 0;
        outline: 0;
        background: transparent;
        color: #243447;
        font-size: 13px;
    }

    .dreams-search input::placeholder {
        color: #8b95a1;
    }

    .dreams-kbd {
        display: inline-grid;
        min-width: 32px;
        height: 22px;
        place-items: center;
        border-radius: 6px;
        background: #eef2f5;
        color: #475569;
        font-size: 11px;
        font-weight: 750;
        line-height: 1;
    }

    .dreams-spacer {
        flex: 1 1 auto;
        min-width: 16px;
    }

    .dreams-store-wrap,
    .dreams-action-wrap,
    .dreams-profile-wrap,
    .dreams-utility-wrap {
        position: relative;
        flex: 0 0 auto;
    }

    .dreams-store-btn {
        width: 128px;
        height: 36px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px;
        border: 1px solid var(--pos-border);
        border-radius: 8px;
        background: #ffffff;
        color: #243447;
        cursor: pointer;
        transition: background .18s ease, border-color .18s ease;
    }

    .dreams-store-btn:hover,
    .dreams-util-btn:hover,
    .dreams-profile-btn:hover {
        background: #f8fafc;
    }

    .dreams-store-icon {
        width: 19px;
        height: 19px;
        display: grid;
        place-items: center;
        border-radius: 5px;
        background: #052e16;
        color: #4ade80;
    }

    .dreams-store-label {
        flex: 1 1 auto;
        overflow: hidden;
        color: #243447;
        font-size: 13px;
        font-weight: 650;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dreams-add-btn,
    .dreams-pos-btn {
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 7px;
        color: #ffffff;
        font-size: 13px;
        font-weight: 760;
        text-decoration: none;
        cursor: pointer;
        transition: background .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .dreams-add-btn {
        width: 100px;
        background: var(--pos-orange);
    }

    .dreams-add-btn:hover,
    .dreams-add-btn.is-active {
        background: var(--pos-orange-hover);
        box-shadow: 0 8px 18px rgba(255, 150, 56, .20);
    }

    .dreams-pos-btn {
        width: auto !important;
        padding: 0 14px !important;
        white-space: nowrap !important;
        background: #172a57;
    }

    .dreams-pos-btn:hover {
        background: var(--pos-navy-dark);
        box-shadow: 0 8px 18px rgba(16, 42, 67, .15);
    }

    .dreams-divider {
        width: 1px;
        height: 38px;
        flex: 0 0 1px;
        margin: 0 2px;
        background: var(--pos-border);
    }

    .dreams-util-btn,
    .dreams-profile-btn {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: var(--pos-control);
        color: #163957;
        cursor: pointer;
        text-decoration: none;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .dreams-util-btn:hover,
    .dreams-profile-btn:hover {
        color: var(--pos-navy);
        transform: translateY(-1px);
    }

    .dreams-badge {
        position: absolute;
        top: -7px;
        right: -4px;
        width: 18px;
        height: 18px;
        display: grid;
        place-items: center;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #ff1e1e;
        color: #ffffff;
        font-size: 10px;
        font-weight: 850;
        line-height: 1;
    }

    .dreams-avatar {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        object-fit: cover;
    }

    .dreams-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        min-width: 184px;
        overflow: hidden;
        border: 1px solid rgba(227, 232, 237, .95);
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .12);
        z-index: 90;
    }

    .dreams-dropdown-item,
    .dreams-profile-meta {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        padding: 10px 12px;
        border: 0;
        background: #ffffff;
        color: #243447;
        font-size: 13px;
        text-align: left;
        text-decoration: none;
    }

    .dreams-dropdown-item:hover {
        background: #f8fafc;
    }

    .dreams-profile-meta {
        align-items: flex-start;
        border-bottom: 1px solid var(--pos-border);
    }

    .dreams-profile-name {
        display: block;
        max-width: 156px;
        overflow: hidden;
        color: #102a43;
        font-size: 13px;
        font-weight: 760;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dreams-profile-email {
        display: block;
        max-width: 156px;
        overflow: hidden;
        margin-top: 2px;
        color: #6b7280;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dreams-avatar-fallback {
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        flex: 0 0 32px;
        border-radius: 9px;
        background: var(--pos-orange);
        color: #ffffff;
        font-size: 11px;
        font-weight: 850;
    }

    .dreams-theme-btn {
        display: grid;
        place-items: center;
        width: 38px;
        height: 34px;
        padding: 0;
        border: 1px solid var(--pos-border);
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        cursor: pointer;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .dreams-theme-btn:hover {
        color: var(--pos-navy);
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .dreams-theme-btn.is-active {
        background: var(--pos-orange);
        border-color: var(--pos-orange);
        color: #ffffff;
    }

    .dreams-mega-menu {
        position: absolute;
        top: calc(100% + 16px);
        left: 50%;
        width: 600px;
        min-height: 220px;
        padding: 20px 16px 16px;
        border: 1px solid rgba(227, 232, 237, .9);
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 22px 50px rgba(15, 23, 42, .14);
        transform: translateX(-50%);
        z-index: 100;
    }

    .dreams-mega-grid {
        display: grid;
        grid-template-columns: repeat(6, 86px);
        gap: 10px 8px;
        justify-content: center;
    }

    .dreams-shortcut {
        width: 86px;
        height: 84px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid var(--pos-border);
        border-radius: 8px;
        background: #ffffff;
        color: #243447;
        text-align: center;
        text-decoration: none;
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .dreams-shortcut:hover {
        border-color: rgba(255, 150, 56, .75);
        background: #fff8f1;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
        transform: translateY(-1px);
    }

    .dreams-shortcut-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #eef3f7;
        color: #163957;
        transition: background .18s ease, color .18s ease;
    }

    .dreams-shortcut:hover .dreams-shortcut-icon {
        background: #ffe7d2;
        color: var(--pos-orange-hover);
    }

    .dreams-shortcut-label {
        color: #243447;
        font-size: 12.5px;
        font-weight: 720;
        line-height: 1;
    }

    [x-cloak] {
        display: none !important;
    }

    @media (max-width: 1180px) {
        .dreams-search {
            width: 210px;
            flex-basis: 210px;
        }

        .dreams-main-row {
            gap: 9px;
            padding-right: 16px;
        }

        .dreams-util-optional {
            display: none;
        }
    }

    @media (max-width: 920px) {
        .dreams-name {
            font-size: 21px;
        }

        .dreams-logo-mark {
            width: 32px;
            height: 32px;
        }

        .dreams-search {
            width: 170px;
            flex-basis: 170px;
            margin-left: 4px;
        }

        .dreams-store-btn {
            width: 112px;
        }

        .dreams-divider,
        .dreams-lang-wrap,
        .dreams-message-wrap {
            display: none;
        }

        .dreams-mega-menu {
            width: calc(100vw - 24px);
            left: auto;
            right: -88px;
            transform: none;
        }

        .dreams-mega-grid {
            grid-template-columns: repeat(4, 86px);
        }
    }

    @media (max-width: 720px) {
        .dreams-pos-topbar {
            height: auto;
            min-height: 70px;
            flex-wrap: wrap;
        }

        .dreams-brand {
            height: 70px;
            width: 100%;
            flex: 0 0 100%;
            border-right: 0;
            border-bottom: 1px solid var(--pos-border);
        }

        .dreams-main-row {
            height: 58px;
            width: 100%;
            flex: 0 0 100%;
            padding: 0 12px;
        }

        .dreams-collapse-btn {
            margin-left: 0;
        }

        .dreams-search {
            flex: 1 1 auto;
            width: auto;
            min-width: 120px;
        }

        .dreams-store-wrap,
        .dreams-spacer,
        .dreams-utility-wrap,
        .dreams-profile-wrap {
            display: none;
        }

        .dreams-add-btn,
        .dreams-pos-btn {
            width: 42px;
            padding: 0;
        }

        .dreams-add-btn span,
        .dreams-pos-btn span {
            display: none;
        }

        .dreams-mega-menu {
            right: 0;
        }

        .dreams-mega-grid {
            grid-template-columns: repeat(3, 86px);
        }
    }
</style>

<div
    class="dreams-pos-topbar"
    x-data="{
        addMenuOpen: false,
        storeOpen: false,
        langOpen: false,
        messagesOpen: false,
        notificationsOpen: false,
        profileOpen: false,
        currentTheme: localStorage.getItem('theme') || 'dark',
        setTheme(theme) {
            this.currentTheme = theme;
            localStorage.setItem('theme', theme);
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.style.colorScheme = theme;
            this.$dispatch('theme-changed', theme);
        },
        toggleSidebar() {
            if (! window.Alpine || ! window.Alpine.store('sidebar')) return;

            window.Alpine.store('sidebar').isOpen
                ? window.Alpine.store('sidebar').close()
                : window.Alpine.store('sidebar').open();
        },
        toggleFullscreen() {
            if (! document.fullscreenElement) {
                document.documentElement.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        },
        initTheme() {
            const storedTheme = localStorage.getItem('theme') || 'dark';
            this.setTheme(storedTheme);
        }
    }"
    x-init="initTheme()"
    x-on:keydown.window.meta.k.prevent="$refs.search?.focus()"
    x-on:keydown.window.ctrl.k.prevent="$refs.search?.focus()"
>
    <div class="dreams-brand" :class="{ 'is-collapsed': $store.sidebar && ! $store.sidebar.isOpen }">
        <a href="{{ route('filament.tenant.pages.admin-dashboard') }}" class="dreams-brand-link" aria-label="HALIS POS dashboard">
            @include('filament.tenant.logo')
        </a>
    </div>

    <div class="dreams-main-row">
        <button type="button" class="dreams-collapse-btn" x-on:click="toggleSidebar()" aria-label="Collapse sidebar" title="Collapse sidebar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m15 18-6-6 6-6" />
                <path d="m20 18-6-6 6-6" />
            </svg>
        </button>

        <label class="dreams-search" for="dreams-global-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
            <input id="dreams-global-search" x-ref="search" type="search" placeholder="{{ trans('common.search') }}" autocomplete="off">
            <span class="dreams-kbd">⌘ K</span>
        </label>

        <div class="dreams-spacer"></div>

        <div class="dreams-store-wrap" x-on:click.outside="storeOpen = false">
            <button type="button" class="dreams-store-btn" x-on:click="storeOpen = ! storeOpen" aria-haspopup="menu" x-bind:aria-expanded="storeOpen">
                <span class="dreams-store-icon" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10h18l-1.6-5.2A2.4 2.4 0 0 0 17.1 3H6.9a2.4 2.4 0 0 0-2.3 1.8L3 10Z" />
                        <path d="M5 10v9h14v-9" />
                        <path d="M9 19v-5h6v5" />
                    </svg>
                </span>
                <span class="dreams-store-label">{{ $activeBranch?->name ?? 'Main Branch' }}</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>
            <div class="dreams-dropdown" x-cloak x-show="storeOpen" x-transition.opacity.scale.origin.top.right style="min-width:200px;max-height:360px;overflow-y:auto;">
                @if(!empty($availableBranches) && count($availableBranches) > 0)
                    @foreach($availableBranches as $branch)
                        <form action="{{ route('tenant.switch-branch') }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="branch" value="{{ $branch->id }}">
                            <button type="submit" class="dreams-dropdown-item" style="{{ $activeBranch?->id === $branch->id ? 'background:#f0f9ff;font-weight:600;' : '' }}">
                                <span style="flex:1;text-align:left;">{{ $branch->name }}</span>
                                @if($activeBranch?->id === $branch->id)
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                @endif
                            </button>
                        </form>
                    @endforeach
                @else
                    <div class="dreams-dropdown-item" style="color:#9ca3af;">No branches found</div>
                @endif
            </div>
        </div>

        <div class="dreams-action-wrap" x-on:click.outside="addMenuOpen = false">
            <button type="button" class="dreams-add-btn" x-bind:class="{ 'is-active': addMenuOpen }" x-on:click="addMenuOpen = ! addMenuOpen" aria-haspopup="menu" x-bind:aria-expanded="addMenuOpen">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v8" />
                    <path d="M8 12h8" />
                </svg>
                <span>{{ trans('common.add') }} New</span>
            </button>

            <div class="dreams-mega-menu" x-cloak x-show="addMenuOpen" x-transition.opacity.scale.origin.top>
                <div class="dreams-mega-grid">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="dreams-shortcut">
                            <span class="dreams-shortcut-icon" aria-hidden="true">
                                @switch($action['icon'])
                                    @case('category')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7v7H4z"/><path d="M13 6h7v7h-7z"/><path d="M4 15h7v3H4z"/><path d="M13 15h7v3h-7z"/></svg>
                                        @break
                                    @case('package')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                                        @break
                                    @case('bag')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l1 12H5L6 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
                                        @break
                                    @case('cart')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2l2.2 10.5a2 2 0 0 0 2 1.5h7.9a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                                        @break
                                    @case('file')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                                        @break
                                    @case('receipt')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v18l2-1 2 1 2-1 2 1 2-1 2 1 2-1V3Z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
                                        @break
                                    @case('return')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 0 12h-1"/></svg>
                                        @break
                                    @case('user')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                        @break
                                    @case('users')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21a6 6 0 0 0-12 0"/><circle cx="10" cy="8" r="4"/><path d="M22 21a6 6 0 0 0-5-5.9"/><path d="M17 4.2a4 4 0 0 1 0 7.6"/></svg>
                                        @break
                                    @case('shield')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M9 12h6"/><path d="M12 9v6"/></svg>
                                        @break
                                    @case('supplier')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                                        @break
                                    @case('truck')
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v10H3z"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <span class="dreams-shortcut-label">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <a href="/tenant/sales-terminal" class="dreams-pos-btn" title="Open POS">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="12" rx="2" />
                <path d="M8 20h8" />
                <path d="M12 16v4" />
            </svg>
            <span>{{ trans('sales.pos') }}</span>
        </a>

        <span class="dreams-divider" aria-hidden="true"></span>

        <div class="dreams-utility-wrap dreams-lang-wrap" x-on:click.outside="langOpen = false">
            <button type="button" class="dreams-util-btn" x-on:click="langOpen = ! langOpen" aria-label="Language" title="Language">
                <img src="https://flagcdn.com/w20/{{ $currentLangFlag }}.png" alt="{{ strtoupper($currentLangCode) }}" width="20" height="14" style="border-radius:2px;">
            </button>
            <div class="dreams-dropdown" x-cloak x-show="langOpen" x-transition.opacity.scale.origin.top.right style="min-width:200px;max-height:360px;overflow-y:auto;">
                @foreach($availableLanguages as $lang)
                    <form action="{{ route('tenant.switch-language') }}" method="POST" style="margin:0;">
                        @csrf
                        <input type="hidden" name="language" value="{{ $lang['code'] }}">
                        <button type="submit" class="dreams-dropdown-item" style="{{ $currentLangCode === $lang['code'] ? 'background:#f0f9ff;font-weight:600;' : '' }}">
                            <img src="https://flagcdn.com/w20/{{ $lang['flag'] }}.png" alt="{{ $lang['code'] }}" width="20" height="14" style="border-radius:2px;flex-shrink:0;">
                            <span style="flex:1;text-align:left;">{{ $lang['name'] }}</span>
                            @if($currentLangCode === $lang['code'])
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <button type="button" class="dreams-util-btn dreams-util-optional" x-on:click="toggleFullscreen()" aria-label="Fullscreen" title="Fullscreen">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 3H5a2 2 0 0 0-2 2v3" />
                <path d="M21 8V5a2 2 0 0 0-2-2h-3" />
                <path d="M3 16v3a2 2 0 0 0 2 2h3" />
                <path d="M16 21h3a2 2 0 0 0 2-2v-3" />
            </svg>
        </button>

        <div class="dreams-utility-wrap dreams-message-wrap" x-on:click.outside="messagesOpen = false">
            <button type="button" class="dreams-util-btn" x-on:click="messagesOpen = ! messagesOpen" aria-label="Messages" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="m3 7 9 6 9-6" />
                </svg>
                @if($unreadEmailCount > 0)
                <span class="dreams-badge">{{ $unreadEmailCount > 99 ? '99+' : $unreadEmailCount }}</span>
                @endif
            </button>
            <div class="dreams-dropdown" x-cloak x-show="messagesOpen" x-transition.opacity.scale.origin.top.right style="min-width:320px;max-height:400px;overflow-y:auto;">
                <div style="padding:10px 14px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:600;color:#111827;">{{ trans('email.inbox') }}</span>
                    @if($unreadEmailCount > 0)
                    <span style="font-size:11px;font-weight:600;color:#fff;background:#2563eb;border-radius:10px;padding:2px 8px;">{{ $unreadEmailCount }}</span>
                    @endif
                </div>
                @if(!empty($latestUnreadEmails))
                    @foreach($latestUnreadEmails as $unreadEmail)
                    <a href="{{ url('/tenant/email-page') }}" class="dreams-dropdown-item" style="padding:10px 14px;border-bottom:1px solid #f3f4f6;display:block;text-decoration:none;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($unreadEmail['from_name'], 0, 2)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $unreadEmail['from_name'] }}</div>
                                <div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $unreadEmail['subject'] }}</div>
                                <div style="font-size:11px;color:#9ca3af;margin-top:2px;">{{ $unreadEmail['received_at'] }}</div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                @else
                <div style="padding:20px 14px;text-align:center;color:#9ca3af;font-size:13px;">
                    {{ trans('email.no_messages') }}
                </div>
                @endif
                <a href="{{ url('/tenant/email-page') }}" class="dreams-dropdown-item" style="padding:10px 14px;text-align:center;font-weight:600;color:#2563eb;display:block;text-decoration:none;">
                    {{ trans('common.view_all') }} {{ trans('email.title') }}
                </a>
            </div>
        </div>

        <div class="dreams-profile-wrap" x-on:click.outside="profileOpen = false">
            <button type="button" class="dreams-profile-btn" x-on:click="profileOpen = ! profileOpen" aria-label="User profile" title="User profile">
                @if ($avatarUrl)
                    <img class="dreams-avatar" src="{{ $avatarUrl }}" alt="{{ $userName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                    <span class="dreams-avatar-fallback" style="display:none;">{{ $initials }}</span>
                @else
                    <span class="dreams-avatar-fallback">{{ $initials }}</span>
                @endif
            </button>
            <div class="dreams-dropdown" x-cloak x-show="profileOpen" x-transition.opacity.scale.origin.top.right>
                <div class="dreams-profile-meta">
                    @if ($avatarUrl)
                        <img class="dreams-avatar" src="{{ $avatarUrl }}" alt="{{ $userName }}" style="width:34px; height:34px; flex:0 0 34px; border-radius:9px; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                        <span class="dreams-avatar-fallback" style="display:none;">{{ $initials }}</span>
                    @else
                        <span class="dreams-avatar-fallback">{{ $initials }}</span>
                    @endif
                    <span>
                        <span class="dreams-profile-name">{{ $userName }}</span>
                        <span class="dreams-profile-email">{{ $userEmail }}</span>
                    </span>
                </div>
                <a href="{{ route('filament.tenant.pages.admin-dashboard') }}" class="dreams-dropdown-item">{{ trans('navigation.dashboard') }}</a>

                <a href="/tenant/account-management" class="dreams-dropdown-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                    Account Management
                </a>

                <a href="/tenant/business-management" class="dreams-dropdown-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 21V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14" />
                        <path d="M2 21h20" />
                        <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                        <path d="M9 21v-4h6v4" />
                    </svg>
                    {{ trans('navigation.business_management') }}
                </a>

                <a href="/tenant/manage-tax-and-efris" class="dreams-dropdown-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                        <path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.05.05a2.15 2.15 0 1 1-3.04 3.04l-.05-.05a1.8 1.8 0 0 0-1.98-.36 1.8 1.8 0 0 0-1.1 1.65V21.4a2.15 2.15 0 1 1-4.3 0v-.08a1.8 1.8 0 0 0-1.1-1.65 1.8 1.8 0 0 0-1.98.36l-.05.05a2.15 2.15 0 1 1-3.04-3.04l.05-.05A1.8 1.8 0 0 0 4.6 15a1.8 1.8 0 0 0-1.65-1.1H2.85a2.15 2.15 0 1 1 0-4.3h.08A1.8 1.8 0 0 0 4.6 8.5a1.8 1.8 0 0 0-.36-1.98l-.05-.05a2.15 2.15 0 1 1 3.04-3.04l.05.05a1.8 1.8 0 0 0 1.98.36 1.8 1.8 0 0 0 1.1-1.65V2.1a2.15 2.15 0 1 1 4.3 0v.08a1.8 1.8 0 0 0 1.1 1.65 1.8 1.8 0 0 0 1.98-.36l.05-.05a2.15 2.15 0 1 1 3.04 3.04l-.05.05a1.8 1.8 0 0 0-.36 1.98 1.8 1.8 0 0 0 1.65 1.1h.08a2.15 2.15 0 1 1 0 4.3h-.08A1.8 1.8 0 0 0 19.4 15Z" />
                    </svg>
                    Settings (Tax & EFRIS)
                </a>

                <button type="button" class="dreams-dropdown-item" x-on:click="window.dreamsLockScreen && window.dreamsLockScreen.lock()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="10" rx="2" />
                        <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                    </svg>
                    {{ trans('navigation.lock_screen') ?? 'Lock Screen' }}
                </button>

                <div class="dreams-dropdown-item" style="display:block; padding:0;">
                    <div style="display:flex; gap:8px; padding:8px 12px;">
                        <button type="button" class="dreams-theme-btn" title="Light mode" aria-label="Light mode" x-on:click="setTheme('light')" x-bind:class="{ 'is-active': currentTheme === 'light' }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2" /><path d="M12 20v2" /><path d="m4.93 4.93 1.41 1.41" /><path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" /><path d="M20 12h2" /><path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" />
                            </svg>
                        </button>
                        <button type="button" class="dreams-theme-btn" title="Dark mode" aria-label="Dark mode" x-on:click="setTheme('dark')" x-bind:class="{ 'is-active': currentTheme === 'dark' }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('filament.tenant.auth.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dreams-dropdown-item">{{ trans('auth.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
