@props(['product'])

<div class="flex items-center gap-3 py-2">
    @if($product->image)
        <img 
            src="{{ Storage::disk('public')->url($product->image) }}" 
            alt="{{ $product->name }}"
            class="w-10 h-10 rounded object-cover flex-shrink-0"
            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product->name) }}&background=random'"
        />
    @else
        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
            <span class="text-gray-500 text-xs font-medium">{{ substr($product->name, 0, 2) }}</span>
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <div class="font-medium text-gray-900 truncate">{{ $product->name }}</div>
        <div class="text-sm text-gray-500">{{ $product->sku }}</div>
    </div>
    <div class="text-sm font-medium text-gray-700">
        {{ number_format($product->price) }} IQD
    </div>
</div>

