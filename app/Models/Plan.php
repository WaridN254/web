<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'plans';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly', 'currency',
        'is_active', 'is_default', 'sort_order',
        'max_users', 'max_branches', 'max_products', 'max_transactions', 'max_storage', 'trial_days',
        'online_store_enabled', 'custom_domain_enabled', 'advanced_reports_enabled',
        'cloud_backup_enabled', 'api_access_enabled', 'multi_unit_enabled', 'efris_enabled',
        'loyalty_enabled', 'wallet_enabled', 'multi_branch_enabled',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'max_users' => 'integer',
            'max_branches' => 'integer',
            'max_products' => 'integer',
            'max_transactions' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
            'online_store_enabled' => 'boolean',
            'custom_domain_enabled' => 'boolean',
            'advanced_reports_enabled' => 'boolean',
            'cloud_backup_enabled' => 'boolean',
            'api_access_enabled' => 'boolean',
            'multi_unit_enabled' => 'boolean',
            'efris_enabled' => 'boolean',
            'loyalty_enabled' => 'boolean',
            'wallet_enabled' => 'boolean',
            'multi_branch_enabled' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }
}
