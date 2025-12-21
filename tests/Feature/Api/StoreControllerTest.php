<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('stores index returns successful response', function () {
    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data',
        ]);
});

test('stores index returns correct response structure', function () {
    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJson([
            'status' => true,
            'message' => 'Stores retrieved successfully',
            'data' => [],
        ]);
});

test('stores index returns all stores', function () {
    Store::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('stores index returns store with correct data', function () {
    $store = Store::factory()->create([
        'name' => 'My Store',
        'slug' => 'my-store',
        'bio' => 'Store description',
        'type' => 'physical',
    ]);

    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJsonFragment([
            'id' => $store->id,
            'name' => 'My Store',
            'slug' => 'my-store',
            'bio' => 'Store description',
            'products_count' => 0,
        ]);
});

test('stores index includes products count', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJsonFragment([
            'id' => $store->id,
            'products_count' => 5,
        ]);
});

test('stores index returns empty data when no stores exist', function () {
    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJson([
            'status' => true,
            'data' => [],
        ]);
});

