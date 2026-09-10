<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'purchase_orders';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'purchase_date' => 'datetime',
        'supplier_invoice_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'due_date' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            // When items are created/updated, they will call recalculateTotals directly.
            // This saving event ensures updates to discount/tax/shipping on the parent
            // will immediately update the grand total.
            $subtotal = $order->items()->sum('total');
            $order->subtotal = $subtotal;
            $order->grand_total = $subtotal - floatval($order->discount) + floatval($order->tax) + floatval($order->shipping);
            $order->due_amount = floatval($order->grand_total) - floatval($order->paid_amount);
            
            if (floatval($order->paid_amount) <= 0) {
                $order->payment_status = 'unpaid';
            } elseif (floatval($order->paid_amount) >= floatval($order->grand_total)) {
                $order->payment_status = 'paid';
            } else {
                $order->payment_status = 'partially_paid';
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function recalculateTotals()
    {
        $subtotal = $this->items()->sum('total');
        $this->subtotal = $subtotal;
        $this->grand_total = $subtotal - floatval($this->discount) + floatval($this->tax) + floatval($this->shipping);
        $this->due_amount = floatval($this->grand_total) - floatval($this->paid_amount);
        
        if (floatval($this->paid_amount) <= 0) {
            $this->payment_status = 'unpaid';
        } elseif (floatval($this->paid_amount) >= floatval($this->grand_total)) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partially_paid';
        }

        $this->saveQuietly();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id');
    }

    public function recalculatePayments()
    {
        $paid = $this->payments()->sum('amount');
        $this->paid_amount = $paid;
        $this->due_amount = floatval($this->grand_total) - $paid;

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid >= floatval($this->grand_total)) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partially_paid';
        }

        $this->saveQuietly();
    }

    public function updateReceivingStatus()
    {
        $totalOrdered = $this->items()->sum('quantity');
        $totalReceived = $this->items()->sum('received_quantity');

        if ($totalReceived <= 0) {
            $this->receiving_status = 'not_received';
        } elseif ($totalReceived >= $totalOrdered) {
            $this->receiving_status = 'fully_received';
            $this->received_at = now();
        } else {
            $this->receiving_status = 'partially_received';
        }

        $this->saveQuietly();
    }
}
