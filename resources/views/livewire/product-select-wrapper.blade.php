@php
    $plan_id = $plan_id ?? null;
    $store_id = $store_id ?? null;
@endphp

<div x-data="{ 
    selectedProducts: [],
    init() {
        // Listen for product selection changes from Livewire component
        Livewire.on('product-selection-changed', (detail) => {
            this.selectedProducts = detail.selectedIds || detail;
            this.updateHiddenInput();
        });
    },
    updateHiddenInput() {
        const hiddenInput = document.querySelector('input[name=\"product_ids\"]');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(this.selectedProducts);
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}">
    <div style="width: 100%;">
        @livewire('product-select', ['plan_id' => $plan_id, 'store_id' => $store_id], key('product-select-'.$plan_id.'-'.$store_id))
    </div>
</div>

