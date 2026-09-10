<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Opening Stock — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'opening_stock', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Opening Stock</h2>
            <p class="text-sm text-gray-500 mb-6">Record your initial inventory counts.</p>

            <form method="POST" action="{{ route('onboarding.save-opening-stock') }}" class="space-y-5">
                @csrf

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 text-center mb-2">
                    <svg class="w-16 h-16 mx-auto text-indigo-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2">Add opening stock after adding products.</p>
                    <p class="text-xs text-gray-400">You can record stock counts from the Stock Audit page in your dashboard.</p>
                </div>

                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="has_stock" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-900">I have existing stock to record</p>
                        <p class="text-xs text-gray-400">We'll help you set up a stock audit</p>
                    </div>
                </label>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.step', 'products') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Save & Continue</button>
                    <a href="{{ route('onboarding.skip', 'opening_stock') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-50 transition">Skip</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
