<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Invite Team — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'team', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Invite Team</h2>
            <p class="text-sm text-gray-500 mb-6">Add team members so they can log in.</p>

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 text-center mb-6">
                <svg class="w-16 h-16 mx-auto text-indigo-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm text-gray-600 mb-2">You can invite team members from the Users page.</p>
                <p class="text-xs text-gray-400">Go to Settings &rarr; Users &amp; Roles after setup.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('onboarding.step', 'opening_stock') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Back</a>
                <button type="submit" form="skip-form" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">Continue</button>
            </div>

            <form id="skip-form" method="POST" action="{{ route('onboarding.save-team') }}" class="hidden">@csrf</form>
        </div>
    </div>
</body>
</html>
