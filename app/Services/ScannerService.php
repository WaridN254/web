<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScannerService
{
    /**
     * Resolve a barcode string to a product (and optionally a variant).
     * Returns ['product' => Product, 'variant' => ProductVariant|null] or null.
     */
    public function resolveBarcode(string $barcode, ?string $tenantId = null): ?array
    {
        $barcode = trim($barcode);
        if (empty($barcode)) return null;

        $tenantId = $tenantId ?? auth()->user()?->tenant_id;

        // 1. Check products.barcode
        $product = Product::where('barcode', $barcode)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->first();

        if ($product) {
            return ['product' => $product, 'variant' => null];
        }

        // 2. Check product_variants.barcode
        $variant = ProductVariant::where('barcode', $barcode)
            ->with('product')
            ->first();

        if ($variant && $variant->product) {
            return ['product' => $variant->product, 'variant' => $variant];
        }

        return null;
    }

    /**
     * Resolve a serial number to a product serial record.
     */
    public function resolveSerial(string $serialNumber, ?string $tenantId = null, ?string $branchId = null): ?ProductSerial
    {
        $serialNumber = trim($serialNumber);
        if (empty($serialNumber)) return null;

        $tenantId = $tenantId ?? auth()->user()?->tenant_id;

        return ProductSerial::where('serial_number', $serialNumber)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('product')
            ->first();
    }

    /**
     * Check if a barcode is a serial number format (heuristic).
     * Serial numbers typically contain letters and are longer than standard barcodes.
     */
    public function guessScanType(string $input): string
    {
        $input = trim($input);

        // Pure numeric, 8-14 digits = likely EAN/UPC barcode
        if (preg_match('/^\d{8,14}$/', $input)) {
            return 'barcode';
        }

        // Contains letters + numbers, could be serial
        if (preg_match('/^[A-Z]{2,}[-]?[A-Z0-9]+/i', $input) && strlen($input) > 6) {
            return 'serial';
        }

        // Short numeric = could be either, treat as barcode
        if (strlen($input) <= 14) {
            return 'barcode';
        }

        // Default: try barcode first, then serial
        return 'barcode';
    }

    /**
     * Process a scan input. Returns a structured result.
     */
    public function processScan(string $input, ?string $tenantId = null, ?string $branchId = null): array
    {
        $input = trim($input);
        if (empty($input)) {
            return ['status' => 'empty', 'message' => 'No input received'];
        }

        $scanType = $this->guessScanType($input);

        // Try as barcode first
        $result = $this->resolveBarcode($input, $tenantId);
        if ($result) {
            return [
                'status' => 'product_found',
                'type' => 'barcode',
                'product' => $result['product'],
                'variant' => $result['variant'],
                'input' => $input,
            ];
        }

        // Try as serial number
        $serial = $this->resolveSerial($input, $tenantId, $branchId);
        if ($serial) {
            return [
                'status' => 'serial_found',
                'type' => 'serial',
                'serial' => $serial,
                'product' => $serial->product,
                'input' => $input,
            ];
        }

        // Try barcode as serial (some scanners scan the serial directly)
        if ($scanType === 'serial') {
            $serial = $this->resolveSerial($input, $tenantId, $branchId);
            if ($serial) {
                return [
                    'status' => 'serial_found',
                    'type' => 'serial',
                    'serial' => $serial,
                    'product' => $serial->product,
                    'input' => $input,
                ];
            }
        }

        return [
            'status' => 'not_found',
            'type' => 'unknown',
            'input' => $input,
            'message' => "Barcode \"{$input}\" not found",
        ];
    }

    /**
     * Validate that a serial is available for sale.
     */
    public function validateSerialForSale(ProductSerial $serial): array
    {
        if ($serial->status !== 'available') {
            $reasons = [
                'sold' => 'This unit has already been sold.',
                'reserved' => 'This unit is reserved.',
                'returned' => 'This unit has been returned.',
                'damaged' => 'This unit is damaged.',
            ];
            return [
                'valid' => false,
                'reason' => $reasons[$serial->status] ?? 'Serial is not available.',
            ];
        }

        return ['valid' => true, 'reason' => null];
    }
}
