<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payment Methods — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'payment_methods', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Payment Methods</h2>
            <p class="text-sm text-gray-500 mb-6">Choose how customers can pay.</p>

            <form method="POST" action="{{ route('onboarding.save-payment-methods') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    @php
                        $methods = ['Cash', 'Mobile Money', 'Card', 'Wallet', 'Bank Transfer', 'Credit'];
                    @endphp
                    @foreach ($methods as $method)
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 transition">
                            <input type="checkbox" name="methods[]" value="{{ $method }}" class="w-4 h-4 text-indigo-600 rounded" {{ in_array($method, old('methods', ['Cash', 'Mobile Money'])) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-900">{{ $method }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.step', 'receipt') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Save & Continue</button>
                    <a href="{{ route('onboarding.skip', 'payment_methods') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-50 transition">Skip</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
