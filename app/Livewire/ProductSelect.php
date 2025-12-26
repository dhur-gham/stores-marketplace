<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductSelect extends Component
{
    public array $products = [];

    public array $selectedIds = [];

    public string $search = '';

    public string $currency = 'IQD';

    public ?int $plan_id = null;

    public ?int $store_id = null;

    public function mount(?int $plan_id = null, ?int $store_id = null, array $selected = [])
    {
        $this->plan_id = $plan_id;
        $this->store_id = $store_id;
        $this->selectedIds = $selected;
        $this->loadProducts();
    }

    public function loadProducts()
    {
        if (! $this->store_id) {
            $this->products = [];

            return;
        }

        // Get product IDs already in this plan
        $existing_product_ids = [];
        if ($this->plan_id) {
            $existing_product_ids = DB::table('discount_plan_products')
                ->where('plan_id', $this->plan_id)
                ->pluck('product_id')
                ->toArray();
        }

        $query = Product::query()
            ->where('store_id', $this->store_id)
            ->when(! empty($existing_product_ids), function ($q) use ($existing_product_ids) {
                return $q->whereNotIn('id', $existing_product_ids);
            })
            ->limit(50);

        $this->products = $query
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'image' => $product->image ? Storage::disk('public')->url($product->image) : null,
                ];
            })
            ->toArray();
    }

    public function toggleProduct(int $productId): void
    {
        if (in_array($productId, $this->selectedIds)) {
            $this->selectedIds = array_filter(
                $this->selectedIds,
                fn ($id) => $id !== $productId
            );
        } else {
            $this->selectedIds[] = $productId;
        }

        $this->selectedIds = array_values($this->selectedIds);
        $this->dispatch('product-selection-changed', selectedIds: $this->selectedIds);
    }

    public function removeProduct(int $productId): void
    {
        $this->selectedIds = array_values(array_filter(
            $this->selectedIds,
            fn ($id) => $id !== $productId
        ));

        $this->dispatch('product-selection-changed', selectedIds: $this->selectedIds);
    }

    public function clearAll(): void
    {
        $this->selectedIds = [];
        $this->dispatch('product-selection-changed', selectedIds: $this->selectedIds);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function getFilteredProducts(): array
    {
        if (empty(trim($this->search))) {
            return $this->products;
        }

        $query = strtolower($this->search);

        return array_filter($this->products, function ($product) use ($query) {
            return stripos($product['name'], $query) !== false ||
                   stripos($product['sku'], $query) !== false;
        });
    }

    public function getSelectedProducts(): array
    {
        return array_filter($this->products, function ($product) {
            return in_array($product['id'], $this->selectedIds);
        });
    }

    public function getInitials(string $name): string
    {
        return strtoupper(substr($name, 0, 2));
    }

    public function formatPrice(int $price): string
    {
        return number_format($price);
    }

    public function render()
    {
        return view('livewire.product-select', [
            'filteredProducts' => $this->getFilteredProducts(),
            'selectedProducts' => $this->getSelectedProducts(),
        ]);
    }
}
