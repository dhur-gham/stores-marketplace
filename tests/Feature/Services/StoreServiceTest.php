<?php

use App\Enums\StoreType;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\StoreService;

test('get_all_stores returns empty array when no stores exist', function () {
    $service = new StoreService();

    $result = $service->get_all_stores();

    expect($result)->toBeArray()->toBeEmpty();
});

test('get_all_stores returns all stores', function () {
    Store::factory()->count(3)->create();

    $service = new StoreService();

    $result = $service->get_all_stores();

    expect($result)->toBeArray()->toHaveCount(3);
});

test('get_all_stores returns correct store structure', function () {
    $store = Store::factory()->create([
        'name' => 'Test Store',
        'slug' => 'test-store',
        'bio' => 'Test bio',
        'image' => 'https://example.com/image.jpg',
        'type' => 'physical',
    ]);

    $service = new StoreService();

    $result = $service->get_all_stores();

    expect($result)->toHaveCount(1);
    expect($result[0])->toMatchArray([
        'id' => $store->id,
        'name' => 'Test Store',
        'slug' => 'test-store',
        'bio' => 'Test bio',
        'image' => 'https://example.com/image.jpg',
        'type' => 'physical',
        'products_count' => 0,
    ]);
});

test('get_all_stores includes products count', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new StoreService();

    $result = $service->get_all_stores();

    expect($result[0]['products_count'])->toBe(5);
});

test('get_all_stores returns correct products count for multiple stores', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
    ]);

    Product::factory()->count(7)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
    ]);

    $service = new StoreService();

    $result = $service->get_all_stores();

    $store1_result = collect($result)->firstWhere('id', $store1->id);
    $store2_result = collect($result)->firstWhere('id', $store2->id);

    expect($store1_result['products_count'])->toBe(3);
    expect($store2_result['products_count'])->toBe(7);
});

test('get_all_stores only returns expected keys', function () {
    Store::factory()->create();

    $service = new StoreService();

    $result = $service->get_all_stores();

    expect($result[0])->toHaveKeys([
        'id',
        'name',
        'slug',
        'bio',
        'image',
        'type',
        'products_count',
    ]);
});
