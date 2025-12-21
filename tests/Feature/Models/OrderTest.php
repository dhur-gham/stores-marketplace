<?php

use App\Models\Order;
use App\Models\User;
use App\Models\Store;
use App\Models\City;
use App\Models\OrderItem;
use App\Enums\OrderStatus;

test('can create an order', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 150.50,
        'delivery_price' => 10.00,
        'status' => 'new',
    ]);

    expect($order->total)->toBe('150.50')
        ->and($order->delivery_price)->toBe('10.00')
        ->and($order->status)->toBe(OrderStatus::New)
        ->and($order->exists)->toBeTrue();
});

test('order belongs to user', function () {
    $user = User::factory()->create(['name' => 'Order Customer']);
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->user)->toBeInstanceOf(User::class)
        ->and($order->user->name)->toBe('Order Customer')
        ->and($order->user_id)->toBe($user->id);
});

test('order belongs to store', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create(['name' => 'Order Store']);
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->store)->toBeInstanceOf(Store::class)
        ->and($order->store->name)->toBe('Order Store')
        ->and($order->store_id)->toBe($store->id);
});

test('order belongs to city', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create(['name' => 'Delivery City']);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->city)->toBeInstanceOf(City::class)
        ->and($order->city->name)->toBe('Delivery City')
        ->and($order->city_id)->toBe($city->id);
});

test('order has order items relationship', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    OrderItem::factory()->count(3)->create([
        'order_id' => $order->id,
    ]);

    expect($order->order_items)->toHaveCount(3)
        ->and($order->order_items->first())->toBeInstanceOf(OrderItem::class);
});

test('order status is cast to enum', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'status' => 'pending',
    ]);

    expect($order->status)->toBeInstanceOf(OrderStatus::class)
        ->and($order->status)->toBe(OrderStatus::Pending);
});

test('order total is cast to decimal', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 123.456,
    ]);

    expect($order->total)->toBe('123.46');
});

test('order delivery price is cast to decimal', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'delivery_price' => 15.999,
    ]);

    expect($order->delivery_price)->toBe('16.00');
});

test('order can have all status types', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $statuses = [
        'new' => OrderStatus::New,
        'pending' => OrderStatus::Pending,
        'processing' => OrderStatus::Processing,
        'completed' => OrderStatus::Completed,
        'cancelled' => OrderStatus::Cancelled,
        'refunded' => OrderStatus::Refunded,
    ];

    foreach ($statuses as $statusValue => $statusEnum) {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'city_id' => $city->id,
            'status' => $statusValue,
        ]);

        expect($order->status)->toBe($statusEnum);
    }
});

test('order requires user_id', function () {
    $store = Store::factory()->create();
    $city = City::factory()->create();

    expect(fn () => Order::factory()->create([
        'user_id' => null,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('order requires store_id', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();

    expect(fn () => Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => null,
        'city_id' => $city->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('order can have zero delivery price', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'delivery_price' => 0,
        'total' => 100.00,
    ]);

    expect($order->delivery_price)->toBe('0.00')
        ->and($order->exists)->toBeTrue();
});

test('multiple orders can belong to same user', function () {
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

    expect($order1->user_id)->toBe($user->id)
        ->and($order2->user_id)->toBe($user->id)
        ->and($user->orders)->toHaveCount(2);
});

test('multiple orders can belong to same store', function () {
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

    expect($order1->store_id)->toBe($store->id)
        ->and($order2->store_id)->toBe($store->id)
        ->and($store->orders)->toHaveCount(2);
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

test('order total is always formatted to 2 decimal places', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 99.999,
    ]);

    expect($order->total)->toBe('100.00');
});
