<x-filament-panels::page>
    @php
        $order = $this->record;
        $status_history = $this->getStatusHistory();
    @endphp

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('orders.pages.edit.order_details') }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('orders.fields.customer') }}</label>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($order->customer->name ?? 'N', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $order->customer->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->customer->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Store Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('orders.fields.store') }}</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $order->store->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('orders.fields.address') }}</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p class="text-gray-900 dark:text-white">{{ $order->address ?? 'N/A' }}</p>
                                @if($order->city)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $order->city->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('orders.pages.edit.order_items') }}</h2>
                    </div>

                    <!-- Existing Items -->
                    <div class="space-y-3 mb-6">
                        @foreach($this->order_items as $index => $item)
                            <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @if($item['product_image'])
                                        <img 
                                            src="{{ $item['product_image'] }}" 
                                            alt="{{ $item['product_name'] }}"
                                            class="w-16 h-16 md:w-20 md:h-20 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                                        >
                                    @else
                                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center border border-gray-200 dark:border-gray-700">
                                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1 truncate">
                                        {{ $item['product_name'] }}
                                    </h3>
                                    @if($item['product_sku'])
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            SKU: {{ $item['product_sku'] }}
                                        </p>
                                    @endif
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                        <span class="font-medium">{{ $item['quantity'] }}</span>
                                        <span class="text-gray-400 dark:text-gray-500">×</span>
                                        <span>{{ number_format($item['price']) }} IQD</span>
                                    </div>
                                </div>
                                
                                <!-- Subtotal -->
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format($item['subtotal']) }} IQD
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ __('orders.messages.subtotal') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('orders.pages.edit.order_summary') }}</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-700 dark:text-gray-300">
                            <span>{{ __('orders.messages.subtotal') }}:</span>
                            <span>{{ number_format(collect($this->order_items)->sum('subtotal')) }} IQD</span>
                        </div>
                        <div class="flex justify-between text-gray-700 dark:text-gray-300">
                            <span>{{ __('orders.messages.delivery') }}:</span>
                            <span>
                                <input 
                                    type="number" 
                                    wire:model="data.delivery_price"
                                    min="0"
                                    class="w-32 px-2 py-1 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm text-right"
                                >
                                <span class="ml-2">IQD</span>
                            </span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ __('orders.messages.total') }}:</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ number_format(collect($this->order_items)->sum('subtotal') + ($this->data['delivery_price'] ?? 0)) }} IQD
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('orders.pages.edit.status') }}</h2>
                    <div class="mb-4">
                        @include('filament.resources.orders.components.status-badge', ['status' => $order->status])
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('orders.pages.edit.change_status') }}</label>
                            <select 
                            wire:model.live="data.status"
                            wire:change="onStatusChanged($event.target.value)"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2">
                            @foreach(\App\Enums\OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ __('orders.status.'.$status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status Timeline Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('orders.pages.edit.status_history') }}</h2>
                    <div class="space-y-4">
                        @foreach($status_history as $index => $history)
                            <div class="relative {{ $index < count($status_history) - 1 ? 'pb-6 border-l-2 border-gray-200 dark:border-gray-700' : '' }}">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $history['label'] }}
                                        </p>
                                        @if($history['status'])
                                            @include('filament.resources.orders.components.status-badge', ['status' => $history['status']])
                                        @endif
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $history['created_at']->diffForHumans() }}
                                            @if(isset($history['changed_by']) && $history['changed_by'])
                                                · {{ __('orders.pages.edit.changed_by') }} {{ $history['changed_by']->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('orders.pages.edit.order_information') }}</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('orders.pages.edit.order_id') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white ml-2">#{{ $order->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('orders.pages.edit.created') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('orders.pages.edit.last_updated') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $order->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-6 flex justify-end gap-3">
            <a 
                href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('index') }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium">
                {{ __('orders.pages.edit.cancel') }}
            </a>
            <button 
                type="submit"
                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all font-medium">
                {{ __('orders.pages.edit.save_changes') }}
            </button>
        </div>
    </form>
</x-filament-panels::page>

