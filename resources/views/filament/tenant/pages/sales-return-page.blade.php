<x-filament-panels::page>
    <div class="space-y-6">
        @livewire('barcode-scanner-input', ['mode' => 'return'], key('return-scanner'))

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sales Return</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display refunded and returned sales from the live transaction records.</p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
