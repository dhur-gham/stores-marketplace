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

    /**
     * Get paginated products from all stores with filtering and sorting.
     *
     * @return array{paginator: LengthAwarePaginator, data: array<int, array<string, mixed>>}
     */
    public function get_all_products(
        int $per_page = 15,
        ?string $search = null,
        ?int $store_id = null,
        ?string $type = null,
        ?int $price_min = null,
        ?int $price_max = null,
        ?string $sort_by = null,
        ?string $sort_order = 'desc'
    ): array {
        $query = Product::query()->with('store');

        // Search by product name
        if ($search && trim($search) !== '') {
            $query->where('name', 'like', '%'.trim($search).'%');
        }

        // Filter by store
        if ($store_id !== null) {
            $query->where('store_id', $store_id);
        }

        // Always filter to active products only (customers should not see inactive or draft products)
        $query->where('status', ProductStatus::Active);

        // Filter by type
        if ($type !== null && in_array($type, ['digital', 'physical'], true)) {
            $query->where('type', $type);
        }

        // Filter by price range
        if ($price_min !== null) {
            $query->where('price', '>=', $price_min);
        }
        if ($price_max !== null) {
            $query->where('price', '<=', $price_max);
        }

        // Sorting
        $sort_order = strtolower($sort_order) === 'asc' ? 'asc' : 'desc';
        if ($sort_by === 'name') {
            $query->orderBy('name', $sort_order);
        } elseif ($sort_by === 'price') {
            $query->orderBy('price', $sort_order);
        } elseif ($sort_by === 'store_name') {
            $query->orderByRaw('(SELECT name FROM stores WHERE stores.id = products.store_id) '.$sort_order);
        } elseif ($sort_by === 'created_at') {
            $query->orderBy('created_at', $sort_order);
        } else {
            // Default: latest first
            $query->latest();
        }

        $paginator = $query->paginate($per_page);

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
                'store' => [
                    'id' => $product->store->id,
                    'name' => $product->store->name,
                    'slug' => $product->store->slug,
                    'image' => $product->store->image ? asset('storage/'.$product->store->image) : null,
                ],
            ])
            ->toArray();

        return [
            'paginator' => $paginator,
            'data' => $data,
        ];
    }
}
