<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Store;

class StoreService
{
    /**
     * Get all stores with their product count.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_all_stores(): array
    {
        return Store::query()
            ->withCount('products')
            ->get()
            ->map(fn (Store $store) => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'bio' => $store->bio,
                'image' => $store->image,
                'type' => $store->type->value,
                'products_count' => $store->products_count,
            ])
            ->toArray();
    }
}

