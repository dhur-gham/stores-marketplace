<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreService
{
    /**
     * Get paginated stores with their product count.
     *
     * @return array{paginator: LengthAwarePaginator, data: array<int, array<string, mixed>>}
     */
    public function get_all_stores(int $per_page = 15): array
    {
        $paginator = Store::query()
            ->withCount('products')
            ->paginate($per_page);

        $data = $paginator->getCollection()
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

        return [
            'paginator' => $paginator,
            'data' => $data,
        ];
    }
}
