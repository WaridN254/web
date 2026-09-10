<div x-data="{ open: @entangle('isOpen') }" class="relative">
    <!-- Add New Button -->
    <button
        @click="open = true"
        class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition duration-200"
    >
        <span class="mr-2">+</span>
        Add New
    </button>

    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 bg-black/50 z-40"
        @click="open = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="open"
        x-transition
        class="absolute top-12 right-0 bg-white dark:bg-slate-800 rounded-lg shadow-xl z-50 p-6 min-w-[600px]"
    >
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Create</h3>
            <button
                @click="open = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Quick Create Grid -->
        <div class="grid grid-cols-6 gap-4 mb-6">
            @foreach($this->quickActions as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-orange-500 hover:bg-orange-50 dark:hover:bg-slate-700 transition duration-200 text-center group"
                >
                    @php
                        $iconClass = match($action['icon']) {
                            'heroicon-o-tag' => 'text-blue-500',
                            'heroicon-o-cube' => 'text-purple-500',
                            'heroicon-o-clipboard-document' => 'text-green-500',
                            'heroicon-o-shopping-cart' => 'text-red-500',
                            'heroicon-o-banknotes' => 'text-yellow-500',
                            'heroicon-o-document-text' => 'text-indigo-500',
                            'heroicon-o-arrow-uturn-left' => 'text-pink-500',
                            'heroicon-o-user' => 'text-cyan-500',
                            'heroicon-o-users' => 'text-teal-500',
                            'heroicon-o-document-currency-dollar' => 'text-emerald-500',
                            'heroicon-o-truck' => 'text-orange-500',
                            'heroicon-o-arrow-left-right' => 'text-violet-500',
                            default => 'text-gray-500',
                        };
                    @endphp
                    <svg class="w-8 h-8 mb-2 {{ $iconClass }} group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                    </svg>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Branch Selector -->
        <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Branch</label>
            <select class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="">-- Select a branch --</option>
                <option value="1">Main Branch</option>
                <option value="2">Downtown Branch</option>
                <option value="3">Mall Branch</option>
            </select>
        </div>
    </div>
</div>
