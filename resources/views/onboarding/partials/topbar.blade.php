<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg width="32" height="32" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#6366f1"/><path d="M12 12h12v12H12z" fill="white"/><path d="M15 15h6v6h-6z" fill="rgba(99,102,241,0.4)"/></svg>
            <span class="text-lg font-bold text-gray-900">HALIS Setup</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-400">{{ $progress['completed_count'] }}/{{ $progress['total'] }}</span>
            <form method="POST" action="{{ route('logout') }}" class="text-sm">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-gray-600 transition">Logout</button>
            </form>
        </div>
    </div>
</div>
<div class="bg-white border-b border-gray-100">
    <div class="max-w-5xl mx-auto">
        <div class="h-1 bg-gray-100">
            <div class="h-1 bg-indigo-600 transition-all duration-500" style="width: {{ $progress['percentage'] }}%"></div>
        </div>
    </div>
</div>
