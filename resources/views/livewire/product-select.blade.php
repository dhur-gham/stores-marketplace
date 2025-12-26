<div class="space-y-4">
    <!-- Selected Products Section -->
    @if(count($selectedProducts) > 0)
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Selected ({{ count($selectedProducts) }})
                </span>
                <button
                    type="button"
                    wire:click="clearAll"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                >
                    Clear all
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($selectedProducts as $product)
                    <div class="group inline-flex items-center gap-2 pl-2 pr-3 py-2 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        @if(!empty($product['image']))
                            <img
                                src="{{ $product['image'] }}"
                                alt="{{ $product['name'] }}"
                                style="width: 28px; height: 28px; border-radius: 8px; object-fit: cover; border: 2px solid white;"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product['name']) }}&background=random'"
                            />
                        @else
                            <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(to bottom right, rgb(96 165 250), rgb(99 102 241)); display: flex; align-items: center; justify-content: center; border: 2px solid white;">
                                <span style="font-size: 10px; font-weight: bold; color: white;">
                                    {{ $this->getInitials($product['name']) }}
                                </span>
                            </div>
                        @endif
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200 max-w-[150px] truncate">
                            {{ $product['name'] }}
                        </span>
                        <button
                            type="button"
                            wire:click="removeProduct({{ $product['id'] }})"
                            class="ml-1 p-0.5 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200"
                            aria-label="Remove {{ $product['name'] }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search Input -->
    <div class="relative">
        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search products by name or SKU..."
            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm focus:border-blue-400 dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-50 dark:focus:ring-blue-900/20 transition-all duration-200 outline-none text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500 text-gray-900 dark:text-gray-100"
        />
        @if(!empty($search))
            <button
                type="button"
                wire:click="clearSearch"
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @endif
    </div>

    <!-- Products List -->
    <div class="relative">
        <div class="max-h-[450px] overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="p-2 space-y-1.5">
                @forelse($filteredProducts as $product)
                    @php
                        $isSelected = in_array($product['id'], $selectedIds);
                    @endphp
                    <button
                        type="button"
                        wire:click="toggleProduct({{ $product['id'] }})"
                        style="width: 100%; display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 12px; border: 2px solid {{ $isSelected ? 'rgb(147 197 253)' : 'rgb(229 231 235)' }}; background: {{ $isSelected ? 'linear-gradient(to bottom right, rgb(239 246 255), rgb(238 242 255))' : 'white' }}; text-align: left; transition: all 0.2s;"
                    >
                        <div style="position: relative; flex-shrink: 0; width: 56px; height: 56px;">
                            @if(!empty($product['image']))
                                <img
                                    src="{{ $product['image'] }}"
                                    alt="{{ $product['name'] }}"
                                    style="width: 56px; height: 56px; border-radius: 12px; object-fit: cover; {{ $isSelected ? 'border: 2px solid rgb(147 197 253);' : 'border: 1px solid rgb(229 231 235);' }}"
                                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product['name']) }}&background=random'"
                                />
                            @else
                                <div style="width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; {{ $isSelected ? 'background: linear-gradient(to bottom right, rgb(96 165 250), rgb(99 102 241)); border: 2px solid rgb(147 197 253);' : 'background: linear-gradient(to bottom right, rgb(229 231 235), rgb(209 213 219)); border: 1px solid rgb(229 231 235);' }}">
                                    <span style="font-size: 14px; font-weight: bold; {{ $isSelected ? 'color: white;' : 'color: rgb(75 85 99);' }}">
                                        {{ $this->getInitials($product['name']) }}
                                    </span>
                                </div>
                            @endif
                            @if($isSelected)
                                <div style="position: absolute; top: -4px; right: -4px; width: 20px; height: 20px; background: rgb(59 130 246); border-radius: 9999px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                    <svg style="width: 12px; height: 12px; color: white;" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 2px; color: {{ $isSelected ? 'rgb(17 24 39)' : 'rgb(31 41 55)' }};">
                                {{ $product['name'] }}
                            </div>
                            <div style="font-size: 12px; color: rgb(107 114 128); font-weight: 500;">
                                SKU: {{ $product['sku'] }}
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0;">
                            <div style="font-size: 14px; font-weight: bold; color: {{ $isSelected ? 'rgb(37 99 235)' : 'rgb(55 65 81)' }};">
                                {{ $this->formatPrice($product['price']) }}
                            </div>
                            <div style="font-size: 12px; color: rgb(156 163 175); font-weight: 500;">
                                {{ $currency }}
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                            {{ !empty($search) ? 'No products found' : 'No products available' }}
                        </p>
                        @if(!empty($search))
                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                Try searching with different keywords
                            </p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('product-selection-changed', (detail) => {
        const hiddenInput = document.querySelector('input[name="product_ids"]');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(detail.selectedIds);
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });
</script>
@endscript
