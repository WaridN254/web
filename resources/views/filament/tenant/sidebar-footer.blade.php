<div x-data class="sidebar-footer" style="display:flex; align-items:center; gap:10px; padding:14px 8px 0; margin-top:auto; color:var(--muted); font-size:13px;">
    @php($sidebarAvatarUrl = auth()->user()?->getFilamentAvatarUrl())
    @if ($sidebarAvatarUrl)
        <img src="{{ $sidebarAvatarUrl }}" alt="" style="width:36px; height:36px; flex:0 0 36px; border-radius:50%; object-fit:cover;">
    @else
        <div class="avatar" style="display:grid; width:36px; height:36px; flex:0 0 auto; place-items:center; border-radius:50%; background:var(--orange); color:#fff; font-weight:800;">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
    @endif
    <div class="user" x-show="$store.sidebar.isOpen" x-cloak style="min-width:0;">
        <strong style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:14px; color:var(--text);">
            {{ auth()->user()->name ?? 'Admin User' }}
        </strong>
        <span style="display:block; margin-top:2px; color:var(--muted); font-size:11px;">
            {{ auth()->user()->email ?? 'admin@example.com' }}
        </span>
    </div>
    <form method="POST" action="{{ route('filament.tenant.auth.logout') }}" x-show="$store.sidebar.isOpen" x-cloak style="margin-left:auto;">
        @csrf
        <button type="submit" class="logout-mark" title="Logout" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px; height:20px;">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
            </svg>
        </button>
    </form>
</div>
