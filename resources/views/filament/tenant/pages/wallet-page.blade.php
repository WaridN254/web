<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Customer Wallets') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage customer wallet balances across all branches') }}</p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
