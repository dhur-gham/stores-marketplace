<x-filament-panels::page>
    @php
        $order = $this->record;
        $products = $this->getProducts();
        $status_history = $this->getStatusHistory();
    @endphp

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Order Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer</label>
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Store</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $order->store->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Delivery Address</label>
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
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Order Items</h2>
                    </div>

                    <!-- Existing Items -->
                    <div class="space-y-3 mb-6">
                        @foreach($this->order_items as $index => $item)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        wire:model.live="order_items.{{ $index }}.quantity"
                                        wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                        min="1"
                                        class="w-20 px-2 py-1 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
                                    >
                                    <span class="text-gray-500 dark:text-gray-400">×</span>
                                    <input 
                                        type="number" 
                                        wire:model.live="order_items.{{ $index }}.price"
                                        wire:change="updateItemPrice({{ $index }}, $event.target.value)"
                                        min="0"
                                        class="w-24 px-2 py-1 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
                                    >
                                    <span class="text-gray-500 dark:text-gray-400">IQD</span>
                                    <span class="w-24 text-right font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($item['subtotal']) }} IQD
                                    </span>
                                    <button 
                                        type="button"
                                        wire:click="removeOrderItem({{ $index }})"
                                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Add New Item -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Add New Item</h3>
                        <div class="flex gap-2">
                            <select 
                                wire:model="new_item_product_id"
                                class="flex-1 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - {{ number_format($product->price) }} IQD</option>
                                @endforeach
                            </select>
                            <input 
                                type="number" 
                                wire:model="new_item_quantity"
                                min="1"
                                placeholder="Qty"
                                class="w-20 px-2 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            >
                            <input 
                                type="number" 
                                wire:model="new_item_price"
                                min="0"
                                placeholder="Price"
                                class="w-24 px-2 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            >
                            <button 
                                type="button"
                                wire:click="addOrderItem"
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all font-medium">
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Order Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-700 dark:text-gray-300">
                            <span>Subtotal:</span>
                            <span>{{ number_format(collect($this->order_items)->sum('subtotal')) }} IQD</span>
                        </div>
                        <div class="flex justify-between text-gray-700 dark:text-gray-300">
                            <span>Delivery:</span>
                            <span>
                                <input 
                                    type="number" 
                                    wire:model.live="data.delivery_price"
                                    wire:change="calculateTotal"
                                    min="0"
                                    class="w-32 px-2 py-1 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm text-right"
                                >
                                <span class="ml-2">IQD</span>
                            </span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total:</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ number_format($this->getOrderTotal()) }} IQD
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Status</h2>
                    <div class="mb-4">
                        @include('filament.resources.orders.components.status-badge', ['status' => $order->status])
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Change Status</label>
                        <select 
                            wire:model.live="data.status"
                            wire:change="onStatusChanged($event.target.value)"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2">
                            @foreach(\App\Enums\OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ ucfirst($status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status Timeline Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Status History</h2>
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
                                                · Changed by {{ $history['changed_by']->name }}
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
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Order Information</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Order ID:</span>
                            <span class="font-semibold text-gray-900 dark:text-white ml-2">#{{ $order->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Created:</span>
                            <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Last Updated:</span>
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
                Cancel
            </a>
            <button 
                type="submit"
                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all font-medium">
                Save Changes
            </button>
        </div>
    </form>
</x-filament-panels::page>

