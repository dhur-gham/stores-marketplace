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
        'price' => 99.99,
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
            'price' => '99.99',
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
