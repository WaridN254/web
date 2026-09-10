<?php

namespace App\Filament\Tenant\Resources\Products\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Products\ProductResource;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\VariantAttribute;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditProduct extends EditRecord
{
    use HasPermission;
    protected static string $resource = ProductResource::class;

    protected array $pendingImages = ['uploads' => [], 'urls' => []];
    protected array $variantAttributeIds = [];

    public array $generatedVariantRows = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['image_uploads'] = $this->record->images()
            ->whereNotNull('image_path')
            ->orderBy('sort_order')
            ->pluck('image_path')
            ->all();

        $urls = $this->record->images()
            ->whereNotNull('image_url')
            ->orderBy('sort_order')
            ->pluck('image_url')
            ->all();

        if (! $this->record->images()->exists()
            && $this->record->image_url
            && filter_var($this->record->image_url, FILTER_VALIDATE_URL)) {
            $urls[] = $this->record->image_url;
        }

        $data['image_urls'] = implode("\n", $urls);

        if ($this->record->has_variants) {
            $data['variant_attribute_ids'] = $this->record->variantAttributes()->pluck('variant_attributes.id')->toArray();

            $sellingPrice = (float) ($this->record->billing_price ?? 0);
            $this->generatedVariantRows = $this->record->variants()
                ->with('values.attribute', 'values.attributeValue')
                ->get()
                ->map(fn ($v) => [
                    'attribute_name' => $v->values->map(fn ($vv) => $vv->attribute->name)->implode(', '),
                    'value_name' => $v->values->map(fn ($vv) => $vv->attributeValue->value)->implode(', '),
                    'sku' => $v->sku,
                    'quantity' => (float) $v->current_stock,
                    'price' => (float) ($v->custom_selling_price ?? $sellingPrice),
                ])
                ->toArray();

            $this->dispatch('variant-rows-updated', rows: $this->generatedVariantRows);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingImages = [
            'uploads' => $data['image_uploads'] ?? [],
            'urls' => $this->splitUrls($data['image_urls'] ?? null),
        ];

        unset($data['image_uploads'], $data['image_urls']);

        $this->variantAttributeIds = $data['variant_attribute_ids'] ?? [];
        unset($data['variant_rows'], $data['variant_attribute_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        ProductImage::syncForProduct(
            $this->record,
            $this->pendingImages['uploads'],
            $this->pendingImages['urls'],
        );

        $tenantId = auth()->user()?->tenant_id;
        Cache::forget("tenant:{$tenantId}:widget_stats");
        Cache::forget("tenant:{$tenantId}:widget_revenue");
        Cache::forget("tenant:{$tenantId}:widget_sales_dashboard");

        if ($this->record->has_variants) {
            $tenantId = auth()->user()?->tenant_id;
            $this->record->variantAttributes()->detach();
            foreach ($this->variantAttributeIds as $attrId) {
                \Illuminate\Support\Facades\DB::table('product_variant_attributes')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'tenant_id' => $tenantId,
                    'product_id' => $this->record->id,
                    'attribute_id' => $attrId,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($this->record->has_variants && !empty($this->generatedVariantRows)) {
            $tenantId = auth()->user()?->tenant_id;
            $sellingPrice = (float) ($this->record->billing_price ?? 0);

            $existingVariantIds = $this->record->variants()->pluck('id')->toArray();
            \App\Models\ProductVariantValue::whereIn('variant_id', $existingVariantIds)->delete();
            $this->record->variants()->whereIn('id', $existingVariantIds)->delete();

            foreach ($this->generatedVariantRows as $row) {
                $attributeName = $row['attribute_name'] ?? '';
                $valueName = $row['value_name'] ?? '';
                $sku = $row['sku'] ?? null;
                $quantity = (float) ($row['quantity'] ?? 0);
                $price = (float) ($row['price'] ?? $sellingPrice);

                if (empty($valueName)) continue;

                if (empty($sku)) {
                    $skuBase = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->record->name), 0, 6));
                    $skuSuffix = strtoupper(substr($valueName, 0, 3));
                    $sku = $skuBase . '-' . $skuSuffix;
                }

                $variant = ProductVariant::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $this->record->id,
                    'name' => $attributeName . ': ' . $valueName,
                    'sku' => $sku,
                    'selling_price_mode' => $price != $sellingPrice ? 'custom' : 'inherited',
                    'custom_selling_price' => $price != $sellingPrice ? $price : null,
                    'cost_price_mode' => 'inherited',
                    'current_stock' => $quantity,
                    'reorder_level' => 0,
                    'track_serial_numbers' => false,
                    'is_active' => true,
                ]);

                $attribute = VariantAttribute::where('tenant_id', $tenantId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($attributeName)])
                    ->first();

                if ($attribute) {
                    $attrValue = $attribute->values()
                        ->whereRaw('LOWER(value) = ?', [strtolower($valueName)])
                        ->first();

                    if ($attrValue) {
                        ProductVariantValue::create([
                            'tenant_id' => $tenantId,
                            'variant_id' => $variant->id,
                            'attribute_id' => $attribute->id,
                            'attribute_value_id' => $attrValue->id,
                        ]);
                    }
                }
            }

            $totalStock = $this->record->variants()->sum('current_stock');
            $this->record->update(['current_stock' => $totalStock]);
        }
    }

    protected function splitUrls(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        return array_values(array_filter(
            preg_split('/\r\n|\r|\n/', $raw),
            fn ($line) => trim((string) $line) !== '',
        ));
    }

    public function generateVariantRows(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $attributeIds = $this->data['variant_attribute_ids'] ?? [];

        if (empty($attributeIds)) {
            $this->generatedVariantRows = [];
            return;
        }

        $attributes = VariantAttribute::query()
            ->whereIn('id', $attributeIds)
            ->with(['values' => fn ($q) => $q->where('is_active', true)->orderBy('value')])
            ->get();

        if ($attributes->isEmpty()) {
            $this->generatedVariantRows = [];
            return;
        }

        $sellingPrice = (float) ($this->data['billing_price'] ?? 0);
        $skuBase = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->data['name'] ?? ''), 0, 6));

        $combinations = [[]];
        foreach ($attributes as $attribute) {
            if ($attribute->values->isEmpty()) continue;
            $newCombinations = [];
            foreach ($combinations as $existing) {
                foreach ($attribute->values as $value) {
                    $newCombinations[] = array_merge($existing, [
                        [
                            'attribute_name' => $attribute->name,
                            'value_name' => $value->value,
                        ]
                    ]);
                }
            }
            $combinations = $newCombinations;
        }

        $rows = [];
        foreach ($combinations as $combo) {
            if (empty($combo)) continue;

            $attrNames = implode(', ', array_column($combo, 'attribute_name'));
            $valNames = implode(', ', array_column($combo, 'value_name'));

            if (empty($valNames)) continue;

            $skuParts = array_map(fn($v) => strtoupper(substr($v, 0, 3)), array_column($combo, 'value_name'));
            $skuSuffix = implode('-', $skuParts);

            $rows[] = [
                'attribute_name' => $attrNames,
                'value_name' => $valNames,
                'sku' => $skuBase . '-' . $skuSuffix,
                'quantity' => 0,
                'price' => $sellingPrice,
            ];
        }

        $this->generatedVariantRows = $rows;
    }


    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved successfully']);
        return null;
    }
}
