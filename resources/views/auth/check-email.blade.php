<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Check Your Email — HALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-8">
    <div class="w-full max-w-md text-center">
        <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-8">
            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">Check your email</h1>
        <p class="text-gray-500 mb-2">
            We've sent an account setup link to
        </p>
        <p class="text-indigo-600 font-semibold mb-6">{{ session('email', 'your email') }}</p>
        <p class="text-sm text-gray-400 mb-8">
            Click the link in the email to create your account and start setting up your POS system.
        </p>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-green-600">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-600">{{ session('error') }}</p>
            </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('registration.resend') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('email', '') }}">
                <button type="submit" class="w-full py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Resend email
                </button>
            </form>
            <a href="{{ route('registration.show') }}" class="block text-sm text-indigo-600 font-medium hover:underline">
                Register a different business
            </a>
        </div>
    </div>
</body>
</html>
