<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingStep extends Model
{
    use HasUuids;

    protected $table = 'onboarding_steps';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id', 'current_step',
        'business_completed', 'branch_completed', 'pos_completed',
        'tax_completed', 'receipt_completed', 'payment_methods_completed',
        'products_completed', 'opening_stock_completed', 'team_completed',
        'hardware_completed', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'business_completed' => 'boolean',
            'branch_completed' => 'boolean',
            'pos_completed' => 'boolean',
            'tax_completed' => 'boolean',
            'receipt_completed' => 'boolean',
            'payment_methods_completed' => 'boolean',
            'products_completed' => 'boolean',
            'opening_stock_completed' => 'boolean',
            'team_completed' => 'boolean',
            'hardware_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
