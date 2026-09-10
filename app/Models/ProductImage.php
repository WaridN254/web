<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasUuids;

    protected $table = 'product_images';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    /**
     * Replace a product's images with the given uploaded file paths and URLs,
     * and keep the legacy products.image_url column pointing at the first one.
     */
    public static function syncForProduct(Product $product, array $uploads, array $urls): void
    {
        $product->images()->delete();

        $sort = 0;

        foreach ($uploads as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $product->images()->create([
                'image_path' => $path,
                'sort_order' => $sort++,
                'tenant_id' => $product->tenant_id,
            ]);
        }

        foreach ($urls as $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }

            $product->images()->create([
                'image_url' => $url,
                'sort_order' => $sort++,
                'tenant_id' => $product->tenant_id,
            ]);
        }

        $first = $product->images()->orderBy('sort_order')->first();

        if ($first) {
            $product->forceFill([
                'image_url' => $first->image_path
                    ? url('product-images/' . $first->image_path)
                    : $first->image_url,
            ])->save();
        }
    }
}