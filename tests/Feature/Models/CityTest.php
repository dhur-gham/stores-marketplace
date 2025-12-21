<?php

use App\Models\City;
use App\Models\Store;
use App\Models\Order;
use App\Models\User;

test('can create a city', function () {
    $city = City::factory()->create([
        'name' => 'Baghdad',
    ]);

    expect($city->name)->toBe('Baghdad')
        ->and($city->exists)->toBeTrue();
});

test('city has stores relationship', function () {
    $city = City::factory()->create(['name' => 'Basra']);
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();

    // Attach stores to city with delivery prices
    $city->stores()->attach($store1->id, ['price' => 5.00]);
    $city->stores()->attach($store2->id, ['price' => 10.00]);

    expect($city->stores)->toHaveCount(2)
        ->and($city->stores->first())->toBeInstanceOf(Store::class);
});

test('city stores relationship includes pivot price', function () {
    $city = City::factory()->create();
    $store = Store::factory()->create();

    $city->stores()->attach($store->id, ['price' => 15.5]);

    $city_store = $city->stores()->first();

    expect($city_store->pivot->price)->toBe(15.5);
});

test('city has orders relationship', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create(['name' => 'Erbil']);

    Order::factory()->count(3)->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($city->orders)->toHaveCount(3)
        ->and($city->orders->first())->toBeInstanceOf(Order::class);
});

test('city name is required', function () {
    expect(fn () => City::factory()->create([
        'name' => null,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('multiple stores can deliver to same city', function () {
    $city = City::factory()->create();
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $store3 = Store::factory()->create();

    $city->stores()->attach($store1->id, ['price' => 5.00]);
    $city->stores()->attach($store2->id, ['price' => 7.50]);
    $city->stores()->attach($store3->id, ['price' => 10.00]);

    expect($city->stores)->toHaveCount(3);
});

test('same store can deliver to multiple cities', function () {
    $store = Store::factory()->create();
    $city1 = City::factory()->create(['name' => 'City One']);
    $city2 = City::factory()->create(['name' => 'City Two']);
    $city3 = City::factory()->create(['name' => 'City Three']);

    $store->cities()->attach($city1->id, ['price' => 5.00]);
    $store->cities()->attach($city2->id, ['price' => 8.00]);
    $store->cities()->attach($city3->id, ['price' => 12.00]);

    expect($store->cities)->toHaveCount(3);
});

test('city can have different delivery prices for different stores', function () {
    $city = City::factory()->create();
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();

    $city->stores()->attach($store1->id, ['price' => 5]);
    $city->stores()->attach($store2->id, ['price' => 15]);

    $stores = $city->stores;

    expect($stores[0]->pivot->price)->toBe(5)
        ->and($stores[1]->pivot->price)->toBe(15);
});

test('city can update delivery price for a store', function () {
    $city = City::factory()->create();
    $store = Store::factory()->create();

    $city->stores()->attach($store->id, ['price' => 10]);

    expect($city->stores()->first()->pivot->price)->toBe(10);

    $city->stores()->updateExistingPivot($store->id, ['price' => 20]);

    $city->refresh();

    expect($city->stores()->first()->pivot->price)->toBe(20);
});

test('city can detach store', function () {
    $city = City::factory()->create();
    $store = Store::factory()->create();

    $city->stores()->attach($store->id, ['price' => 10.00]);

    expect($city->stores)->toHaveCount(1);

    $city->stores()->detach($store->id);

    $city->refresh();

    expect($city->stores)->toHaveCount(0);
});

test('multiple orders can belong to same city', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order1 = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $order2 = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order1->city_id)->toBe($city->id)
        ->and($order2->city_id)->toBe($city->id)
        ->and($city->orders)->toHaveCount(2);
});

test('city can have zero delivery price for a store', function () {
    $city = City::factory()->create();
    $store = Store::factory()->create();

    $city->stores()->attach($store->id, ['price' => 0]);

    expect($city->stores()->first()->pivot->price)->toBe(0);
});

test('city pivot timestamps are recorded', function () {
    $city = City::factory()->create();
    $store = Store::factory()->create();

    $city->stores()->attach($store->id, ['price' => 10.00]);

    $pivot = $city->stores()->first()->pivot;

    expect($pivot->created_at)->not->toBeNull()
        ->and($pivot->updated_at)->not->toBeNull();
});
