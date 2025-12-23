<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
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
                'image' => $product->image ? asset('storage/'.$product->image) : null,
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

    /**
     * Get a single product by ID or slug.
     *
     * @return array<string, mixed>|null
     */
    public function get_product_by_id_or_slug(string|int $identifier): ?array
    {
        $product = is_numeric($identifier)
            ? Product::query()->with('store')->find($identifier)
            : Product::query()->with('store')->where('slug', $identifier)->first();

        if (! $product) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->image ? asset('storage/'.$product->image) : null,
            'description' => $product->description,
            'sku' => $product->sku,
            'status' => $product->status->value,
            'type' => $product->type->value,
            'price' => $product->price,
            'stock' => $product->stock,
            'store' => [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'slug' => $product->store->slug,
                'image' => $product->store->image ? asset('storage/'.$product->store->image) : null,
            ],
        ];
    }

    /**
     * Get the latest products from all stores.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_latest_products(int $limit = 5): array
    {
        $products = Product::query()
            ->with('store')
            ->where('status', ProductStatus::Active)
            ->latest()
            ->limit($limit)
            ->get();

        return $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->image ? asset('storage/'.$product->image) : null,
            'description' => $product->description,
            'price' => $product->price,
            'store' => [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'slug' => $product->store->slug,
            ],
        ])->toArray();
    }
}
