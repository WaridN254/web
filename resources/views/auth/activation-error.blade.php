<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }} — HALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-8">
    <div class="w-full max-w-md text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-8">
            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $title }}</h1>
        <p class="text-gray-500 mb-8">{{ $message }}</p>

        <div class="space-y-3">
            @if (!empty($show_login))
                <a href="{{ route('filament.tenant.auth.login') }}" class="block w-full py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Go to Login
                </a>
            @endif
            @if (!empty($show_resend))
                <form method="POST" action="{{ route('registration.resend') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email ?? '' }}">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        Resend Setup Link
                    </button>
                </form>
            @endif
            <a href="{{ route('registration.show') }}" class="block text-sm text-indigo-600 font-medium hover:underline">
                Register a new business
            </a>
        </div>
    </div>
</body>
</html>
