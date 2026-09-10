<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Setup Complete — HALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-8">
    <div class="w-full max-w-md text-center">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8">
            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-3">You're all set!</h1>
        <p class="text-gray-500 mb-2">
            <strong>{{ $tenant->name }}</strong> is ready to go.
        </p>
        <p class="text-sm text-gray-400 mb-10">
            Your POS system is configured and ready. Start adding products and making sales.
        </p>

        <a href="/tenant"
           class="inline-block w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition text-center">
            Go to Dashboard
        </a>

        <div class="mt-8 grid grid-cols-2 gap-4 text-left">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Add Products</h3>
                <p class="text-xs text-gray-400">Build your product catalog</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Open the Register</h3>
                <p class="text-xs text-gray-400">Start your first sales session</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Invite Team</h3>
                <p class="text-xs text-gray-400">Add cashiers and staff</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Stock Audit</h3>
                <p class="text-xs text-gray-400">Record your opening inventory</p>
            </div>
        </div>
    </div>
</body>
</html>
