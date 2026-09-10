<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hardware — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'hardware', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Hardware</h2>
            <p class="text-sm text-gray-500 mb-6">Set up receipt printers, barcode scanners, and cash drawers.</p>

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 text-center mb-6">
                <svg class="w-16 h-16 mx-auto text-indigo-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <p class="text-sm text-gray-600 mb-2">Hardware setup is optional.</p>
                <p class="text-xs text-gray-400">You can connect printers and scanners later from Settings.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('onboarding.step', 'team') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                <button type="submit" form="skip-form" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Finish Setup</button>
            </div>

            <form id="skip-form" method="POST" action="{{ route('onboarding.save-hardware') }}" class="hidden">@csrf</form>
        </div>
    </div>
</body>
</html>
