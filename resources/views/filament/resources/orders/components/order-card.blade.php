<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ __('orders.messages.order_number') }}{{ $order->id }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
            </div>
            @include('filament.resources.orders.components.status-badge', ['status' => $order->status])
        </div>

        <!-- Customer Info -->
        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($order->customer->name ?? 'N', 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $order->customer->name ?? 'N/A' }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->customer->email ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Store & Location -->
        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 space-y-2">
            <div class="flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="text-gray-700 dark:text-gray-300">{{ $order->store->name ?? 'N/A' }}</span>
            </div>
            @if($order->city)
            <div class="flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-gray-700 dark:text-gray-300">{{ $order->city->name }}</span>
            </div>
            @endif
        </div>

        <!-- Order Items Summary -->
        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('orders.messages.items') }}:</p>
            <div class="space-y-1">
                @forelse($order->order_items->take(3) as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ $item->product->name ?? 'N/A' }} × {{ $item->quantity }}
                        </span>
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ number_format($item->price * $item->quantity) }} IQD
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('orders.messages.no_items') }}</p>
                @endforelse
                @if($order->order_items->count() > 3)
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('orders.messages.more_items', ['count' => $order->order_items->count() - 3]) }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Totals -->
        <div class="mb-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ __('orders.messages.subtotal') }}:</span>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ number_format($order->total - $order->delivery_price) }} IQD
                </span>
            </div>
            @if($order->delivery_price > 0)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ __('orders.messages.delivery') }}:</span>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ number_format($order->delivery_price) }} IQD
                </span>
            </div>
            @endif
            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="font-semibold text-gray-900 dark:text-white">{{ __('orders.messages.total') }}:</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ number_format($order->total) }} IQD
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $order]) }}" 
               class="flex-1 text-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all font-medium text-sm">
                {{ __('orders.messages.view_details') }}
            </a>
        </div>
    </div>
</div>

