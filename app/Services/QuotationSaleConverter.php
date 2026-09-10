<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quotation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationSaleConverter
{
    public function convert(Quotation $quotation): Transaction
    {
        $tenantId = $quotation->tenant_id;

        $transaction = Transaction::create([
            'id' => (string) Str::uuid(),
            'receipt_number' => 'POS-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'session_id' => null,
            'customer_id' => null,
            'customer_name' => $quotation->customer_name,
            'user_id' => auth()->id() ?? $quotation->user_id,
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'discount_amount' => $quotation->discount_amount,
            'total_amount' => $quotation->total_amount,
            'payment_method' => 'cash',
            'amount_paid' => $quotation->total_amount,
            'change_given' => 0,
            'status' => 'completed',
            'notes' => 'Converted from quotation ' . $quotation->quote_number,
            'transaction_date' => now(),
            'order_name' => $quotation->quote_number,
            'document_type' => 'sale',
            'tenant_id' => $tenantId,
            'branch_id' => app(BranchService::class)->getActiveBranchId(),
            'original_transaction_id' => $quotation->id,
        ]);

        $quotationItems = DB::table('quotation_items')
            ->where('tenant_id', $tenantId)
            ->where('quotation_id', $quotation->id)
            ->get();

        foreach ($quotationItems as $item) {
            $product = null;

            if (! empty($item->product_id)) {
                $product = Product::query()->where('tenant_id', $tenantId)->where('id', $item->product_id)->first();
            }

            DB::table('transaction_items')->insert([
                'id' => (string) Str::uuid(),
                'transaction_id' => $transaction->id,
                'product_id' => $item->product_id,
                'service_id' => null,
                'item_type' => $product ? 'product' : 'product',
                'product_name' => $item->product_name ?? ($product->name ?? 'Quoted Product'),
                'quantity' => $item->quantity ?? 1,
                'unit_price' => $item->unit_price ?? 0,
                'cost_price' => $product?->cost_price ?? 0,
                'tax_rate' => $product?->tax_rate ?? 0,
                'tax_amount' => $item->tax_amount ?? 0,
                'discount' => $item->discount_amount ?? 0,
                'line_total' => $item->line_total ?? 0,
                'serial_id' => null,
                'serial_number' => null,
                'original_transaction_item_id' => null,
                'sale_unit_id' => null,
                'sale_unit_name' => null,
                'sale_unit_label' => null,
                'base_quantity' => null,
                'unit_conversion_to_base' => 1,
                'created_at' => now(),
                'sync_status' => 'pending',
                'last_synced_at' => null,
                'tenant_id' => $tenantId,
                'branch_id' => $transaction->branch_id,
            ]);
        }

        $quotation->update([
            'status' => 'approved',
            'converted_transaction_id' => $transaction->id,
        ]);

        return $transaction;
    }
}
