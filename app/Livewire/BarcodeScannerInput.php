<?php

namespace App\Livewire;

use App\Services\ScannerService;
use Livewire\Component;

class BarcodeScannerInput extends Component
{
    public string $scanInput = '';
    public string $mode = 'general'; // sale, receiving, stock_count, transfer, return, serial, general
    public bool $autoFocus = true;
    public bool $continuous = true;
    public ?string $lastScannedProduct = null;
    public ?string $lastScannedMessage = null;
    public string $lastScanStatus = ''; // success, error, warning
    public bool $showCamera = false;

    protected ScannerService $scanner;

    public function boot(): void
    {
        $this->scanner = app(ScannerService::class);
    }

    public function updatedScanInput(): void
    {
        $input = trim($this->scanInput);
        if (empty($input)) return;

        // Debounce: only process if input looks like a complete scan
        // (physical scanners append Enter, camera sends full code)
        $this->processScan($input);
    }

    public function processManualInput(): void
    {
        $this->processScan($this->scanInput);
    }

    public function processScan(string $input): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $branchId = session('active_branch_id');

        $result = $this->scanner->processScan($input, $tenantId, $branchId);

        match ($result['status']) {
            'product_found' => $this->handleProductFound($result),
            'serial_found' => $this->handleSerialFound($result),
            'not_found' => $this->handleNotFound($result),
            default => $this->handleEmpty(),
        };

        $this->scanInput = '';
    }

    private function handleProductFound(array $result): void
    {
        $product = $result['product'];
        $variant = $result['variant'];
        $name = $variant ? "{$product->name} - {$variant->name}" : $product->name;

        $this->lastScannedProduct = $product->id;
        $this->lastScannedMessage = "{$name} found";
        $this->lastScanStatus = 'success';

        $this->dispatch('scanner-product-found', [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'barcode' => $result['input'],
            'mode' => $this->mode,
        ]);
    }

    private function handleSerialFound(array $result): void
    {
        $serial = $result['serial'];
        $product = $result['product'];

        $validation = $this->scanner->validateSerialForSale($serial);

        if (!$validation['valid']) {
            $this->lastScannedMessage = "Serial {$serial->serial_number}: {$validation['reason']}";
            $this->lastScanStatus = 'error';
            $this->dispatch('scanner-serial-error', [
                'serial' => $serial->serial_number,
                'reason' => $validation['reason'],
                'product_id' => $product?->id,
            ]);
            return;
        }

        $this->lastScannedProduct = $product?->id;
        $this->lastScannedMessage = "Serial {$serial->serial_number} — {$product->name}";
        $this->lastScanStatus = 'success';

        $this->dispatch('scanner-serial-found', [
            'serial_id' => $serial->id,
            'serial_number' => $serial->serial_number,
            'product_id' => $product?->id,
            'variant_id' => $serial->variant_id,
            'mode' => $this->mode,
        ]);
    }

    private function handleNotFound(array $result): void
    {
        $this->lastScannedMessage = $result['message'];
        $this->lastScanStatus = 'error';

        $this->dispatch('scanner-not-found', [
            'barcode' => $result['input'],
            'mode' => $this->mode,
        ]);
    }

    private function handleEmpty(): void
    {
        $this->lastScannedMessage = null;
        $this->lastScanStatus = '';
    }

    public function toggleCamera(): void
    {
        $this->showCamera = !$this->showCamera;
    }

    public function render()
    {
        return view('livewire.barcode-scanner-input');
    }
}
