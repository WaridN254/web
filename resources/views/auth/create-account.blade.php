<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Your Account — HALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-white min-h-screen flex">
    <!-- Left: Brand -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 to-purple-700 text-white flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image:url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-12">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="white" fill-opacity="0.2"/><path d="M12 12h12v12H12z" fill="white"/><path d="M15 15h6v6h-6z" fill="rgba(0,0,0,0.2)"/></svg>
                <span class="text-xl font-bold tracking-tight">HALIS</span>
            </div>
            <h1 class="text-4xl font-extrabold leading-tight mb-4">Almost there!</h1>
            <p class="text-lg text-indigo-100 max-w-md">Create your admin account to start using <strong>{{ $business_name }}</strong>.</p>
        </div>
        <div class="relative z-10 text-sm text-indigo-200">
            &copy; {{ date('Y') }} HALIS. All rights reserved.
        </div>
    </div>

    <!-- Right: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <svg width="32" height="32" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#6366f1"/><path d="M12 12h12v12H12z" fill="white"/><path d="M15 15h6v6h-6z" fill="rgba(99,102,241,0.4)"/></svg>
                <span class="text-xl font-bold text-gray-900">HALIS</span>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-1">Create your account</h2>
            <p class="text-gray-500 text-sm mb-8">Set up your login credentials for <strong>{{ $business_name }}</strong>.</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('activation.store', $token) }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" value="{{ $email }}" disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="Minimum 8 characters">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <button type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Create Account & Start Setup
                </button>
            </form>
        </div>
    </div>
</body>
</html>
