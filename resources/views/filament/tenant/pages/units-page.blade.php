<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-white">Units</h2>
                <p class="text-sm text-gray-400">Configured product units</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-700 bg-gray-900/60 p-4">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
