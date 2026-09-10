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
    <!-- Top bar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg width="32" height="32" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#6366f1"/><path d="M12 12h12v12H12z" fill="white"/><path d="M15 15h6v6h-6z" fill="rgba(99,102,241,0.4)"/></svg>
                <span class="text-lg font-bold text-gray-900">HALIS Setup</span>
            </div>
            <div class="text-sm text-gray-400">
                {{ $progress['completed_count'] }}/{{ $progress['total'] }} steps completed
            </div>
        </div>
    </div>

    <!-- Progress bar -->
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto">
            <div class="h-1 bg-gray-100">
                <div class="h-1 bg-indigo-600 transition-all duration-500" style="width: {{ $progress['percentage'] }}%"></div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-5xl mx-auto px-6 py-10">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome, {{ $tenant->name }}!</h1>
            <p class="text-gray-500">Let's set up your POS system in a few quick steps.</p>
        </div>

        <!-- Step cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $stepRoutes = [
                    'business' => 'business',
                    'branch' => 'branch',
                    'pos' => 'pos',
                    'tax' => 'tax',
                    'receipt' => 'receipt',
                    'payment_methods' => 'payment_methods',
                    'products' => 'products',
                    'opening_stock' => 'opening_stock',
                    'team' => 'team',
                    'hardware' => 'hardware',
                ];
            @endphp

            @foreach ($progress['steps'] as $key => $step)
                <a href="{{ route('onboarding.step', $key) }}"
                   class="relative bg-white border rounded-xl p-5 hover:shadow-md transition group {{ $step['completed'] ? 'border-green-200 bg-green-50/30' : 'border-gray-200 hover:border-indigo-300' }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $step['completed'] ? 'bg-green-100' : 'bg-gray-100 group-hover:bg-indigo-100' }}">
                            @if ($step['completed'])
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-sm font-bold {{ $step['required'] ? 'text-indigo-600' : 'text-gray-400' }}">{{ $loop->iteration }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 mb-0.5">{{ $step['label'] }}</h3>
                            <p class="text-xs text-gray-400">
                                {{ $step['completed'] ? 'Completed' : ($step['required'] ? 'Required' : 'Optional') }}
                            </p>
                        </div>
                        @if ($step['required'] && !$step['completed'])
                            <span class="absolute top-2 right-2 w-2 h-2 bg-indigo-500 rounded-full"></span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Skip / Complete -->
        <div class="text-center mt-10 space-y-3">
            @if ($progress['required_remaining'] === 0)
                <a href="{{ route('onboarding.complete') }}"
                   class="inline-block px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm transition">
                    Complete Setup
                </a>
            @else
                <p class="text-sm text-gray-400">
                    Complete all required steps to finish setup.
                </p>
            @endif
        </div>
    </div>
</body>
</html>
