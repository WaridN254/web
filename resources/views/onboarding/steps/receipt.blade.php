<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Receipt Setup — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'receipt', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Receipt Setup</h2>
            <p class="text-sm text-gray-500 mb-6">Customize your printed receipts.</p>

            <form method="POST" action="{{ route('onboarding.save-receipt') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer Message</label>
                    <textarea name="receipt_footer" rows="2" placeholder="Thank you for your purchase!"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('receipt_footer') }}</textarea>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="show_tax_number" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Show tax/TIN number</p>
                            <p class="text-xs text-gray-400">Display your business tax ID on receipts</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="show_cashier_name" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Show cashier name</p>
                            <p class="text-xs text-gray-400">Display the cashier who processed the sale</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="show_customer_name" value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Show customer name</p>
                            <p class="text-xs text-gray-400">Display the customer's name on receipts</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.step', 'tax') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Save & Continue</button>
                    <a href="{{ route('onboarding.skip', 'receipt') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-50 transition">Skip</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
