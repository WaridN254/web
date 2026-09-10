<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Setup — HALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'tax', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Tax Setup</h2>
            <p class="text-sm text-gray-500 mb-6">Configure how taxes are applied.</p>

            <form method="POST" action="{{ route('onboarding.save-tax') }}" class="space-y-5">
                @csrf

                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="charges_tax" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-900">This business charges tax</p>
                        <p class="text-xs text-gray-400">Enable tax calculation on sales</p>
                    </div>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Name</label>
                        <input type="text" name="tax_name" value="{{ old('tax_name', 'VAT') }}" placeholder="VAT"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" value="{{ old('tax_rate', 18) }}" step="0.01" min="0" max="100"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="tax_inclusive" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Tax inclusive pricing</p>
                        <p class="text-xs text-gray-400">Prices already include tax</p>
                    </div>
                </label>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.step', 'pos') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Save & Continue</button>
                    <a href="{{ route('onboarding.skip', 'tax') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-50 transition">Skip</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
