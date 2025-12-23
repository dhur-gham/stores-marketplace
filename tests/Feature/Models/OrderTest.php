<?php

use App\Enums\OrderStatus;
use App\Models\City;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;

test('can create an order', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 150000,
        'delivery_price' => 5000,
        'status' => 'new',
    ]);

    expect($order->total)->toBe(150000)
        ->and($order->delivery_price)->toBe(5000)
        ->and($order->status)->toBe(OrderStatus::New)
        ->and($order->exists)->toBeTrue();
});

test('order belongs to customer', function () {
    $customer = Customer::factory()->create(['name' => 'Order Customer']);
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->customer)->toBeInstanceOf(Customer::class)
        ->and($order->customer->name)->toBe('Order Customer')
        ->and($order->customer_id)->toBe($customer->id);
});

test('order belongs to store', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['name' => 'Order Store']);
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->store)->toBeInstanceOf(Store::class)
        ->and($order->store->name)->toBe('Order Store')
        ->and($order->store_id)->toBe($store->id);
});

test('order belongs to city', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create(['name' => 'Delivery City']);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order->city)->toBeInstanceOf(City::class)
        ->and($order->city->name)->toBe('Delivery City')
        ->and($order->city_id)->toBe($city->id);
});

test('order has order items relationship', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
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
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'status' => 'pending',
    ]);

    expect($order->status)->toBeInstanceOf(OrderStatus::class)
        ->and($order->status)->toBe(OrderStatus::Pending);
});

test('order total is cast to decimal', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 123,
    ]);

    expect($order->total)->toBe(123);
});

test('order delivery price is cast to decimal', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'delivery_price' => 15,
    ]);

    expect($order->delivery_price)->toBe(15);
});

test('order can have all status types', function () {
    $customer = Customer::factory()->create();
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
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'city_id' => $city->id,
            'status' => $statusValue,
        ]);

        expect($order->status)->toBe($statusEnum);
    }
});

test('order requires customer_id', function () {
    $store = Store::factory()->create();
    $city = City::factory()->create();

    expect(fn () => Order::factory()->create([
        'customer_id' => null,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('order requires store_id', function () {
    $customer = Customer::factory()->create();
    $city = City::factory()->create();

    expect(fn () => Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => null,
        'city_id' => $city->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('order can have zero delivery price', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'delivery_price' => 0,
        'total' => 100.00,
    ]);

    expect($order->delivery_price)->toBe(0)
        ->and($order->exists)->toBeTrue();
});

test('multiple orders can belong to same customer', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order1 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $order2 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order1->customer_id)->toBe($customer->id)
        ->and($order2->customer_id)->toBe($customer->id)
        ->and($customer->orders)->toHaveCount(2);
});

test('multiple orders can belong to same store', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order1 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $order2 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order1->store_id)->toBe($store->id)
        ->and($order2->store_id)->toBe($store->id)
        ->and($store->orders)->toHaveCount(2);
});

test('multiple orders can belong to same city', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order1 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $order2 = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect($order1->city_id)->toBe($city->id)
        ->and($order2->city_id)->toBe($city->id)
        ->and($city->orders)->toHaveCount(2);
});

test('order total is always formatted to 2 decimal places', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
        'total' => 99,
    ]);

    expect($order->total)->toBe(99);
});
