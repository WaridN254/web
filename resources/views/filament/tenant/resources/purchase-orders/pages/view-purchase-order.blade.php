<x-filament-panels::page>
    <div class="space-y-6">
        @livewire('barcode-scanner-input', ['mode' => 'receiving'], key('receiving-scanner'))

        {{ $this->infolist }}
    </div>
</x-filament-panels::page>
