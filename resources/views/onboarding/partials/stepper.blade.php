@php
    $steps = [
        'business' => 'Business',
        'branch' => 'Branch',
        'pos' => 'POS',
        'tax' => 'Tax',
        'receipt' => 'Receipt',
        'payment_methods' => 'Payments',
        'products' => 'Products',
        'opening_stock' => 'Stock',
        'team' => 'Team',
        'hardware' => 'Hardware',
    ];
    $currentIdx = array_search($current, array_keys($steps));
@endphp

<div class="flex items-center gap-2 overflow-x-auto pb-2 mb-2 -mx-2 px-2">
    @foreach ($steps as $key => $label)
        @php
            $idx = array_search($key, array_keys($steps));
            $isComplete = $progress['steps'][$key]['completed'] ?? false;
            $isCurrent = $key === $current;
        @endphp
        <a href="{{ route('onboarding.step', $key) }}"
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition
                  {{ $isCurrent ? 'bg-indigo-600 text-white' : ($isComplete ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200') }}">
            @if ($isComplete)
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @endif
            {{ $label }}
        </a>
        @if ($idx < count($steps) - 1)
            <span class="text-gray-300">&rarr;</span>
        @endif
    @endforeach
</div>
