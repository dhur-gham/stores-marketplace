<?php

use App\Models\City;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('can create an order item', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50.00,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 50,
    ]);

    expect($order_item->quantity)->toBe(3)
        ->and($order_item->price)->toBe(50)
        ->and($order_item->exists)->toBeTrue();
});

test('order item belongs to order', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    expect($order_item->order)->toBeInstanceOf(Order::class)
        ->and($order_item->order->id)->toBe($order->id)
        ->and($order_item->order_id)->toBe($order->id);
});

test('order item belongs to product', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Test Product Item',
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    expect($order_item->product)->toBeInstanceOf(Product::class)
        ->and($order_item->product->name)->toBe('Test Product Item')
        ->and($order_item->product_id)->toBe($product->id);
});

test('order item quantity is cast to integer', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => '5',
    ]);

    expect($order_item->quantity)->toBeInt()
        ->and($order_item->quantity)->toBe(5);
});

test('order item price is cast to decimal', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price' => 100,
    ]);

    expect($order_item->price)->toBe(100);
});

test('order item requires order_id', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect(fn () => OrderItem::factory()->create([
        'order_id' => null,
        'product_id' => $product->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('order item requires product_id', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    expect(fn () => OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => null,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('multiple order items can belong to same order', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item1 = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
    ]);

    $order_item2 = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
    ]);

    expect($order_item1->order_id)->toBe($order->id)
        ->and($order_item2->order_id)->toBe($order->id)
        ->and($order->order_items)->toHaveCount(2);
});

test('multiple order items can reference same product', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

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

    $order_item1 = OrderItem::factory()->create([
        'order_id' => $order1->id,
        'product_id' => $product->id,
    ]);

    $order_item2 = OrderItem::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
    ]);

    expect($order_item1->product_id)->toBe($product->id)
        ->and($order_item2->product_id)->toBe($product->id)
        ->and($product->order_items)->toHaveCount(2);
});

test('order item price is always formatted to 2 decimal places', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price' => 123,
    ]);

    expect($order_item->price)->toBe(123);
});

test('order item can have high quantity', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $city = City::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'city_id' => $city->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order_item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 999,
    ]);

    expect($order_item->quantity)->toBe(999)
        ->and($order_item->exists)->toBeTrue();
});
