<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('products index returns successful response', function () {
    $store = Store::factory()->create();

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

test('products index returns correct response structure', function () {
    $store = Store::factory()->create();

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJson([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
            ],
        ]);
});

test('products index returns paginated products for a store', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 5);
});

test('products index returns product with correct data', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'description' => 'Product description',
        'sku' => 'SKU-1234',
        'status' => 'active',
        'type' => 'physical',
        'price' => 99,
        'stock' => 50,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Product description',
            'sku' => 'SKU-1234',
            'status' => 'active',
            'type' => 'physical',
            'price' => 99,
            'stock' => 50,
        ]);
});

test('products index returns empty data when store has no products', function () {
    $store = Store::factory()->create();

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJson([
            'status' => true,
            'data' => [],
            'meta' => [
                'total' => 0,
            ],
        ]);
});

test('products index returns 404 when store does not exist', function () {
    $response = $this->getJson('/api/v1/stores/99999/products');

    $response->assertNotFound();
});

test('products index only returns products for the specified store', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
    ]);

    Product::factory()->count(2)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store1->id}/products");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
});

test('products index respects per_page parameter', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(10)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store->id}/products?per_page=5");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 10)
        ->assertJsonPath('meta.last_page', 2);
});

test('products index returns correct page', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(10)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store->id}/products?per_page=5&page=2");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2);
});

test('products all endpoint returns successful response', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

test('products all accepts search parameter', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Tech Product',
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Fashion Product',
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?search=Tech');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Tech Product');
});

test('products all filter by store_id works', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    Product::factory()->count(2)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->getJson("/api/v1/products?store_id={$store1->id}");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
});

test('products all only returns active products', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'inactive',
    ]);

    Product::factory()->count(1)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    $response = $this->getJson('/api/v1/products');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

test('products all filter by type works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => 'digital',
        'status' => 'active',
    ]);

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => 'physical',
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?type=digital');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

test('products all filter by price_min works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?price_min=75');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.price', 100);
});

test('products all filter by price_max works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?price_max=75');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.price', 50);
});

test('products all filter by price range works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 200,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?price_min=75&price_max=150');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.price', 100);
});

test('products all sort_by parameter works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Zebra Product',
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Apple Product',
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?sort_by=name&sort_order=asc');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Apple Product')
        ->assertJsonPath('data.1.name', 'Zebra Product');
});

test('products all sort_order parameter works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?sort_by=price&sort_order=desc');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.price', 100)
        ->assertJsonPath('data.1.price', 50);
});

test('products all pagination works', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(10)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products?per_page=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 10)
        ->assertJsonPath('meta.last_page', 2);
});

test('products all returns correct product structure with store info', function () {
    $store = Store::factory()->create(['name' => 'Test Store']);
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/products');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [
                0 => [
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
                    'store' => [
                        'id',
                        'name',
                        'slug',
                        'image',
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.0.store.name', 'Test Store');
});

test('products all defaults to active products only', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'inactive',
    ]);

    $response = $this->getJson('/api/v1/products');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});
