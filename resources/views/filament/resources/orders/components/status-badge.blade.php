@php
    $colors = [
        'new' => 'bg-gradient-to-r from-blue-500 to-blue-600 text-white',
        'processing' => 'bg-gradient-to-r from-purple-500 to-purple-600 text-white',
        'dispatched' => 'bg-gradient-to-r from-cyan-500 to-cyan-600 text-white',
        'complete' => 'bg-gradient-to-r from-green-500 to-green-600 text-white',
        'cancelled' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
    ];
    
    $color = $colors[$status->value] ?? $colors['new'];
    $label = __('orders.status.'.$status->value);
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $color }} shadow-sm">
    {{ $label }}
</span>

