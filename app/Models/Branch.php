<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'branches';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $branch) {
            if (empty($branch->tenant_id) && auth()->check() && auth()->user()?->tenant_id) {
                $branch->tenant_id = auth()->user()->tenant_id;
            }

            if (empty($branch->business_id) && auth()->check()) {
                $tenant = auth()->user()?->tenant;

                if ($tenant && $tenant->business_id) {
                    $branch->business_id = $tenant->business_id;
                }
            }

            if (empty($branch->code)) {
                $branch->code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $branch->name ?? 'BRA'), 0, 3));
            }
        });

        static::saved(function (self $branch) {
            if ($branch->is_default) {
                static::query()
                    ->where('tenant_id', $branch->tenant_id)
                    ->where('id', '!=', $branch->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_primary', true);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeForCurrentTenantBusiness(Builder $query): Builder
    {
        if (! auth()->check()) {
            return $query;
        }

        $tenant = auth()->user()?->tenant;

        if (! $tenant) {
            return $query->whereRaw('0 = 1');
        }

        if ($tenant->business_id) {
            return $query->where(function ($branchQuery) use ($tenant) {
                $branchQuery
                    ->where('tenant_id', $tenant->id)
                    ->orWhere('business_id', $tenant->business_id);
            });
        }

        return $query->where('tenant_id', $tenant->id);
    }

    // ─── Helpers ────────────────────────────────────────────

    public function getStockForProduct(string $productId, ?string $variantId = null): BranchStock
    {
        return $this->branchStocks()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->firstOrCreate([
                'tenant_id' => $this->tenant_id,
                'branch_id' => $this->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ], [
                'quantity' => 0,
            ]);
    }

    public function getTotalStockValueAttribute(): float
    {
        return (float) $this->branchStocks()
            ->join('products', 'products.id', '=', 'branch_stocks.product_id')
            ->sum(\Illuminate\Support\Facades\DB::raw('branch_stocks.quantity * products.cost_price'));
    }
}
