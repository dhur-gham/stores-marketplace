<?php

use App\Enums\ProductStatus;
use App\Enums\StoreStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\StoreStatusService;

test('deactivateStoreProducts deactivates all products for a store', function () {
    $store = Store::factory()->create(['status' => StoreStatus::Active]);
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new StoreStatusService;
    $updated_count = $service->deactivateStoreProducts($store);

    expect($updated_count)->toBe(5);

    $store->products()->each(function (Product $product) {
        expect($product->status)->toBe(ProductStatus::Inactive);
    });
});

test('deactivateStoreProducts works with chunks for large datasets', function () {
    $store = Store::factory()->create(['status' => StoreStatus::Active]);
    $user = User::factory()->create();

    // Create 1200 products to test chunking (default chunk size is 500)
    Product::factory()->count(1200)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new StoreStatusService;
    $updated_count = $service->deactivateStoreProducts($store, 500);

    expect($updated_count)->toBe(1200);

    // Verify all products are inactive
    $inactive_count = Product::query()
        ->where('store_id', $store->id)
        ->where('status', ProductStatus::Inactive)
        ->count();

    expect($inactive_count)->toBe(1200);
});

test('deactivateStoreProducts only affects products for the specific store', function () {
    $store1 = Store::factory()->create(['status' => StoreStatus::Active]);
    $store2 = Store::factory()->create(['status' => StoreStatus::Active]);
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->count(2)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new StoreStatusService;
    $service->deactivateStoreProducts($store1);

    // Store1 products should be inactive
    $store1_inactive = Product::query()
        ->where('store_id', $store1->id)
        ->where('status', ProductStatus::Inactive)
        ->count();
    expect($store1_inactive)->toBe(3);

    // Store2 products should still be active
    $store2_active = Product::query()
        ->where('store_id', $store2->id)
        ->where('status', ProductStatus::Active)
        ->count();
    expect($store2_active)->toBe(2);
});

test('store status change to inactive automatically deactivates all products', function () {
    $store = Store::factory()->create(['status' => StoreStatus::Active]);
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    // Change store status to inactive
    $store->update(['status' => StoreStatus::Inactive]);

    // All products should now be inactive
    $store->refresh();
    $inactive_count = Product::query()
        ->where('store_id', $store->id)
        ->where('status', ProductStatus::Inactive)
        ->count();

    expect($inactive_count)->toBe(5);
});

test('store status change to inactive does not run if already inactive', function () {
    $store = Store::factory()->create(['status' => StoreStatus::Inactive]);
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    // Try to update store when it's already inactive (should not trigger deactivation)
    $store->update(['bio' => 'Updated bio']);

    // Products should remain as they were (active)
    $active_count = Product::query()
        ->where('store_id', $store->id)
        ->where('status', ProductStatus::Active)
        ->count();

    expect($active_count)->toBe(3);
});
