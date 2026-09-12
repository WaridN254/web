<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'stock_movements';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public const UPDATED_AT = null;

    protected $casts = [
        'quantity' => 'decimal:3',
        'sale_quantity' => 'decimal:3',
        'base_quantity' => 'decimal:3',
        'movement_date' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($movement) {
            if ($movement->branch_id && $movement->tenant_id) {
                $delta = 0;
                if ($movement->movement_type === 'in') {
                    $delta = $movement->quantity;
                } elseif ($movement->movement_type === 'out') {
                    $delta = -$movement->quantity;
                } elseif ($movement->movement_type === 'adjustment') {
                    $delta = $movement->quantity;
                }

                if ($delta !== 0) {
                    $existing = \App\Models\BranchStock::where([
                        'tenant_id' => $movement->tenant_id,
                        'branch_id' => $movement->branch_id,
                        'product_id' => $movement->product_id,
                        'variant_id' => $movement->variant_id,
                    ])->first();

                    if ($existing) {
                        $existing->quantity += $delta;
                        $existing->save();
                    } else {
                        $product = Product::find($movement->product_id);
                        \App\Models\BranchStock::create([
                            'tenant_id' => $movement->tenant_id,
                            'branch_id' => $movement->branch_id,
                            'product_id' => $movement->product_id,
                            'variant_id' => $movement->variant_id,
                            'quantity' => ($product?->current_stock ?? 0) + $delta,
                        ]);
                    }

                    Product::where('id', $movement->product_id)
                        ->update(['current_stock' => DB::raw("current_stock + ($delta)")]);
                } else {
                    $existing = \App\Models\BranchStock::where([
                        'tenant_id' => $movement->tenant_id,
                        'branch_id' => $movement->branch_id,
                        'product_id' => $movement->product_id,
                        'variant_id' => $movement->variant_id,
                    ])->first();

                    if (!$existing) {
                        $product = Product::find($movement->product_id);
                        \App\Models\BranchStock::create([
                            'tenant_id' => $movement->tenant_id,
                            'branch_id' => $movement->branch_id,
                            'product_id' => $movement->product_id,
                            'variant_id' => $movement->variant_id,
                            'quantity' => $product?->current_stock ?? 0,
                        ]);
                    }
                }
            } else {
                $product = $movement->product;
                if ($product) {
                    $delta = 0;
                    if ($movement->movement_type === 'in') {
                        $delta = $movement->quantity;
                    } elseif ($movement->movement_type === 'out') {
                        $delta = -$movement->quantity;
                    } elseif ($movement->movement_type === 'adjustment') {
                        $delta = $movement->quantity;
                    }

                    if ($delta !== 0) {
                        Product::where('id', $movement->product_id)
                            ->update(['current_stock' => DB::raw("current_stock + ($delta)")]);
                    }
                }
            }
        });
    }
}
