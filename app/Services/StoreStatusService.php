<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Store;

class StoreStatusService
{
    /**
     * Deactivate all products for a store in chunks for better performance.
     *
     * @param  int  $chunk_size  Number of products to process per chunk (default: 500)
     */
    public function deactivateStoreProducts(Store $store, int $chunk_size = 500): int
    {
        $total_updated = 0;

        $store->products()
            ->chunk($chunk_size, function ($products) use (&$total_updated) {
                $product_ids = $products->pluck('id')->toArray();

                $updated = Product::query()
                    ->whereIn('id', $product_ids)
                    ->update(['status' => ProductStatus::Inactive]);

                $total_updated += $updated;
            });

        return $total_updated;
    }
}
