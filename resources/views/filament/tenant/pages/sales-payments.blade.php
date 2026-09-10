<div class="space-y-3">
    @forelse ($payments as $payment)
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ ucfirst((string) $payment->payment_method) }}</span>
                <span class="text-gray-600 dark:text-gray-300">UGX {{ number_format((float) $payment->amount_paid, 2) }}</span>
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $payment->payment_date ? \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
            No payment records found for this sale.
        </div>
    @endforelse
</div>
