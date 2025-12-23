<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StoreType;
use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StoreService
{
    /**
     * Get a single store by ID or slug.
     *
     * @return array<string, mixed>|null
     */
    public function get_store_by_id_or_slug(string|int $identifier): ?array
    {
        $store = is_numeric($identifier)
            ? Store::query()->withCount('products')->find($identifier)
            : Store::query()->withCount('products')->where('slug', $identifier)->first();

        if (! $store) {
            return null;
        }

        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'bio' => $store->bio,
            'image' => $store->image ? asset('storage/'.$store->image) : null,
            'type' => $store->type->value,
            'products_count' => $store->products_count,
        ];
    }

    /**
     * Get paginated stores with their product count.
     *
     * @return array{paginator: LengthAwarePaginator, data: array<int, array<string, mixed>>}
     */
    public function get_all_stores(int $per_page = 15, ?string $search = null): array
    {
        $query = Store::query()->withCount('products');

        if ($search && trim($search) !== '') {
            $query->where('name', 'like', '%'.trim($search).'%');
        }

        $paginator = $query->paginate($per_page);

        $data = $paginator->getCollection()
            ->map(fn (Store $store) => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'bio' => $store->bio,
                'image' => $store->image ? asset('storage/'.$store->image) : null,
                'type' => $store->type->value,
                'products_count' => $store->products_count,
            ])
            ->toArray();

        return [
            'paginator' => $paginator,
            'data' => $data,
        ];
    }

    /**
     * Create a new store.
     *
     * @param  array{name: string, bio?: string|null, image?: string|null, type: StoreType, user_id?: int|null}  $data
     */
    public function create_store(array $data): Store
    {
        $data['slug'] = $this->generate_unique_slug($data['name']);

        return Store::query()->create($data);
    }

    /**
     * Update an existing store.
     *
     * @param  array{name?: string, bio?: string|null, image?: string|null, type?: StoreType}  $data
     */
    public function update_store(Store $store, array $data): Store
    {
        if (isset($data['name']) && $data['name'] !== $store->name) {
            $data['slug'] = $this->generate_unique_slug($data['name'], $store->id);
        }

        $store->update($data);

        return $store->fresh();
    }

    /**
     * Delete a store.
     */
    public function delete_store(Store $store): bool
    {
        return $store->delete();
    }

    /**
     * Generate a unique slug for a store.
     */
    private function generate_unique_slug(string $name, ?int $exclude_id = null): string
    {
        $slug = Str::slug($name);
        $original_slug = $slug;
        $counter = 1;

        while ($this->slug_exists($slug, $exclude_id)) {
            $slug = $original_slug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists.
     */
    private function slug_exists(string $slug, ?int $exclude_id = null): bool
    {
        $query = Store::query()->where('slug', $slug);

        if ($exclude_id !== null) {
            $query->where('id', '!=', $exclude_id);
        }

        return $query->exists();
    }
}
