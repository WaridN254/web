<?php

namespace App\Filament\Tenant\Pages\Concerns;

use App\Models\Product;
use App\Support\Code128;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;

trait PrintsProductLabels
{
    public string $search = '';

    public string $categoryId = '';

    public string $brandId = '';

    public string $paperSize = 'A4';

    public bool $showStoreName = true;

    public bool $showProductName = true;

    public bool $showPrice = true;

    public bool $showReference = true;

    public bool $previewOpen = false;

    public array $items = [];

    protected array $qrCache = [];

    protected array $codeCache = [];

    protected array $skuCache = [];

    public function categories(): array
    {
        return DB::table('categories')
            ->where('tenant_id', auth()->user()?->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    public function brands(): array
    {
        return DB::table('product_brands')
            ->where('tenant_id', auth()->user()?->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    public function paperSizes(): array
    {
        return ['A3', 'A4', 'A5', 'A6'];
    }

    public function labelCols(): int
    {
        return match ($this->paperSize) {
            'A3' => 4,
            'A5' => 3,
            'A6' => 2,
            default => 3,
        };
    }

    public function currency(): string
    {
        return auth()->user()?->tenant?->currency_code
            ?? auth()->user()?->tenant?->settings['currency']
            ?? 'UGX';
    }

    public function storeName(): string
    {
        return auth()->user()?->tenant?->business?->name ?? 'Store';
    }

    public function productCode(Product $product): string
    {
        if (isset($this->codeCache[$product->id])) {
            return $this->codeCache[$product->id];
        }

        $barcode = trim((string) $product->barcode);
        if ($barcode !== '') {
            return $this->codeCache[$product->id] = $barcode;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $product->id);
        $digits = str_pad($digits, 10, '0', STR_PAD_RIGHT);

        return $this->codeCache[$product->id] = substr($digits, 0, 10);
    }

    public function productSku(Product $product): string
    {
        if (isset($this->skuCache[$product->id])) {
            return $this->skuCache[$product->id];
        }

        $sku = trim((string) $product->barcode);
        if ($sku !== '') {
            return $this->skuCache[$product->id] = $sku;
        }

        $short = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', (string) $product->id), 0, 6));

        return $this->skuCache[$product->id] = 'P' . $short;
    }

    public function searchResults(): array
    {
        $query = Product::query()->where('tenant_id', auth()->user()?->tenant_id);

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'ilike', $like)
                    ->orWhere('barcode', 'ilike', $like)
                    ->orWhere('barcode', 'ilike', $like);
            });
        }

        if ($this->categoryId !== '') {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->brandId !== '') {
            $query->where('brand_id', $this->brandId);
        }

        $selected = collect($this->items)->pluck('id')->all();
        if ($selected !== []) {
            $query->whereNotIn('id', $selected);
        }

        return $query->orderBy('name')
            ->limit(8)
            ->with('images')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $this->productCode($product),
                'sku' => $this->productSku($product),
                'price' => (float) ($product->billing_price ?? 0),
                'image' => $product->imageUrl(),
            ])
            ->all();
    }

    public function addProduct(string $id): void
    {
        $product = Product::query()->with('images')->find($id);

        if (! $product) {
            return;
        }

        foreach ($this->items as $item) {
            if ($item['id'] === $product->id) {
                return;
            }
        }

        $this->items[] = [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $this->productCode($product),
            'sku' => $this->productSku($product),
            'ref' => $this->productCode($product),
            'price' => (float) ($product->billing_price ?? 0),
            'image' => $product->imageUrl(),
            'qty' => 1,
        ];
    }

    public function incrementQty(string $id): void
    {
        foreach ($this->items as &$item) {
            if ($item['id'] === $id) {
                $item['qty']++;
                break;
            }
        }
    }

    public function decrementQty(string $id): void
    {
        foreach ($this->items as &$item) {
            if ($item['id'] === $id && $item['qty'] > 1) {
                $item['qty']--;
                break;
            }
        }
    }

    public function removeProduct(string $id): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn ($item) => $item['id'] !== $id
        ));
    }

    public function generate(): void
    {
        $this->previewOpen = true;
    }

    public function closePreview(): void
    {
        $this->previewOpen = false;
    }

    public function resetAll(): void
    {
        $this->items = [];
        $this->search = '';
        $this->categoryId = '';
        $this->brandId = '';
        $this->paperSize = 'A4';
        $this->showStoreName = true;
        $this->showProductName = true;
        $this->showPrice = true;
        $this->showReference = true;
        $this->previewOpen = false;
    }

    public function barcodeSvg(string $code, int $height = 46): string
    {
        return Code128::svg($code, $height);
    }

    public function qrSvg(string $code): string
    {
        if (isset($this->qrCache[$code])) {
            return $this->qrCache[$code];
        }

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => EccLevel::M,
            'scale' => 6,
            'addQuietzone' => true,
            'svgAddFillColor' => false,
        ]);

        return $this->qrCache[$code] = (new QRCode($options))->render($code);
    }
}
