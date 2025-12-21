<?php

use App\Models\Store;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\City;
use App\Enums\StoreType;

test('can create a store', function () {
    $user = User::factory()->create();
    
    $store = Store::factory()->create([
        'name' => 'Tech Store',
        'type' => 'digital',
    ]);

    expect($store->name)->toBe('Tech Store')
        ->and($store->type)->toBe(StoreType::Digital)
        ->and($store->exists)->toBeTrue();
});

test('store has users relationship', function () {
    $store = Store::factory()->create();
    $users = User::factory()->count(2)->create();

    $store->users()->attach($users->pluck('id'));

    expect($store->users)->toHaveCount(2)
        ->and($store->users->first())->toBeInstanceOf(User::class);
});

test('store has products relationship', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect($store->products)->toHaveCount(3)
        ->and($store->products->first())->toBeInstanceOf(Product::class);
});

test('store has orders relationship', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Order::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect($store->orders)->toHaveCount(2)
        ->and($store->orders->first())->toBeInstanceOf(Order::class);
});

test('store has cities relationship with delivery price', function () {
    $store = Store::factory()->create();
    $cities = City::factory()->count(3)->create();

    $store->cities()->attach([
        $cities[0]->id => ['price' => 10.50],
        $cities[1]->id => ['price' => 15.00],
        $cities[2]->id => ['price' => 20.00],
    ]);

    expect($store->cities)->toHaveCount(3)
        ->and($store->cities->first()->pivot->price)->toBeNumeric()
        ->and($store->cities->first())->toBeInstanceOf(City::class);
});

test('store type is cast to enum', function () {
    $store = Store::factory()->create(['type' => 'digital']);

    expect($store->type)->toBeInstanceOf(StoreType::class)
        ->and($store->type)->toBe(StoreType::Digital);
});

test('store isDigital method works correctly', function () {
    $digitalStore = Store::factory()->create(['type' => 'digital']);
    $physicalStore = Store::factory()->create(['type' => 'physical']);

    expect($digitalStore->isDigital())->toBeTrue()
        ->and($physicalStore->isDigital())->toBeFalse();
});

test('store isPhysical method works correctly', function () {
    $physicalStore = Store::factory()->create(['type' => 'physical']);
    $digitalStore = Store::factory()->create(['type' => 'digital']);

    expect($physicalStore->isPhysical())->toBeTrue()
        ->and($digitalStore->isPhysical())->toBeFalse();
});

test('store slug must be unique', function () {
    Store::factory()->create(['slug' => 'unique-store']);

    expect(fn () => Store::factory()->create(['slug' => 'unique-store']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
