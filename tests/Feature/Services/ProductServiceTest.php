<?php

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ProductService;

test('get_products_by_store returns empty array when store has no products', function () {
    $store = Store::factory()->create();

    $service = new ProductService();

    $result = $service->get_products_by_store($store);

    expect($result)->toBeArray()->toBeEmpty();
});

test('get_products_by_store returns all products for a store', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService();

    $result = $service->get_products_by_store($store);

    expect($result)->toBeArray()->toHaveCount(5);
});

test('get_products_by_store returns correct product structure', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'image' => 'https://example.com/image.jpg',
        'description' => 'Test description',
        'sku' => 'SKU-123',
        'status' => ProductStatus::Active,
        'type' => ProductType::Physical,
        'price' => 99.99,
        'stock' => 50,
    ]);

    $service = new ProductService();

    $result = $service->get_products_by_store($store);

    expect($result)->toHaveCount(1);
    expect($result[0])->toMatchArray([
        'id' => $product->id,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'image' => 'https://example.com/image.jpg',
        'description' => 'Test description',
        'sku' => 'SKU-123',
        'status' => 'active',
        'type' => 'physical',
        'price' => '99.99',
        'stock' => 50,
    ]);
});

test('get_products_by_store only returns products for the specified store', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
    ]);

    Product::factory()->count(5)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService();

    $result = $service->get_products_by_store($store1);

    expect($result)->toHaveCount(3);
});

test('get_products_by_store only returns expected keys', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService();

    $result = $service->get_products_by_store($store);

    expect($result[0])->toHaveKeys([
        'id',
        'name',
        'slug',
        'image',
        'description',
        'sku',
        'status',
        'type',
        'price',
        'stock',
    ]);
});

test('get_products_by_store returns products with different statuses', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Draft,
    ]);

    $service = new ProductService();

    $result = $service->get_products_by_store($store);

    expect($result)->toHaveCount(3);

    $statuses = collect($result)->pluck('status')->toArray();
    expect($statuses)->toContain('active', 'inactive', 'draft');
});
