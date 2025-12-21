<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Store;

class ProductService
{
    /**
     * Get all products for a given store.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_products_by_store(Store $store): array
    {
        return $store->products()
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->image,
                'description' => $product->description,
                'sku' => $product->sku,
                'status' => $product->status->value,
                'type' => $product->type->value,
                'price' => $product->price,
                'stock' => $product->stock,
            ])
            ->toArray();
    }
}

