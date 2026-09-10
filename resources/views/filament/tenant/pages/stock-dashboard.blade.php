<x-filament-panels::page>
    <div class="space-y-6">
        @livewire('barcode-scanner-input', ['mode' => 'stock_count'], key('stock-scanner'))

        {{ $this->table }}
    </div>
</x-filament-panels::page>
