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
        ]);
});

test('products index returns all products for a store', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/v1/stores/{$store->id}/products");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
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
        ->assertJsonCount(3, 'data');
});
