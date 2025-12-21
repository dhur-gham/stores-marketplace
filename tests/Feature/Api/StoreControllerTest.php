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
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

test('stores index returns correct response structure', function () {
    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJson([
            'status' => true,
            'message' => 'Stores retrieved successfully',
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
            ],
        ]);
});

test('stores index returns paginated stores', function () {
    Store::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/stores');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
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
            'meta' => [
                'total' => 0,
            ],
        ]);
});

test('stores index respects per_page parameter', function () {
    Store::factory()->count(10)->create();

    $response = $this->getJson('/api/v1/stores?per_page=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 10)
        ->assertJsonPath('meta.last_page', 2);
});

test('stores index returns correct page', function () {
    Store::factory()->count(10)->create();

    $response = $this->getJson('/api/v1/stores?per_page=5&page=2');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2);
});
