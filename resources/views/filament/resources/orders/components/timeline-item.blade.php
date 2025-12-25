@php
    $isLast = $isLast ?? false;
@endphp

<div class="relative pb-8 {{ !$isLast ? 'border-l-2 border-gray-200 dark:border-gray-700' : '' }}">
    <div class="flex gap-4">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold shadow-lg">
                {{ strtoupper(substr($order->customer->name ?? 'N', 0, 1)) }}
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        Order #{{ $order->id }}
                    </p>
                    @include('filament.resources.orders.components.status-badge', ['status' => $order->status])
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {{ $order->customer->name ?? 'N/A' }} • {{ $order->store->name ?? 'N/A' }}
                </p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ number_format($order->total) }} IQD
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $order->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
</div>

