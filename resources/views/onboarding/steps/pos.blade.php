<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>POS Basics — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'pos', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">POS Basics</h2>
            <p class="text-sm text-gray-500 mb-6">Configure your point of sale settings.</p>

            <form method="POST" action="{{ route('onboarding.save-pos') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="UGX">UGX — Ugandan Shilling</option>
                        <option value="KES">KES — Kenyan Shilling</option>
                        <option value="USD">USD — US Dollar</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="price_includes_tax" value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Prices include tax</p>
                            <p class="text-xs text-gray-400">Tax is already included in product prices</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="allow_negative_stock" value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Allow negative stock</p>
                            <p class="text-xs text-gray-400">Sell products even when stock runs out</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="allow_decimal_quantities" value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Decimal quantities</p>
                            <p class="text-xs text-gray-400">Allow selling fractional quantities (e.g. 0.5 kg)</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.step', 'branch') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Back
                    </a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">
                        Save & Continue
                    </button>
                    <a href="{{ route('onboarding.skip', 'pos') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-50 transition">
                        Skip
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
