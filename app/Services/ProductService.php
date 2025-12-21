<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get paginated products for a given store.
     *
     * @return array{paginator: LengthAwarePaginator, data: array<int, array<string, mixed>>}
     */
    public function get_products_by_store(Store $store, int $per_page = 15): array
    {
        $paginator = $store->products()->paginate($per_page);

        $data = $paginator->getCollection()
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

        return [
            'paginator' => $paginator,
            'data' => $data,
        ];
    }
}
