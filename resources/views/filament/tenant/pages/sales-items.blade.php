<div class="space-y-3">
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $item->product_name }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No items found for this sale.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
