<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\StoreType;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

test('place_order creates orders grouped by store', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 100,
        'stock' => 10,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 200,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 100,
    ]);
    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
        'quantity' => 1,
        'price' => 200,
    ]);

    $city = City::factory()->create();
    $store->cities()->attach($city->id, ['price' => 500]);

    $service = app(OrderService::class);

    $orders = $service->place_order($customer, [
        $store->id => [
            'city_id' => $city->id,
            'address' => '123 Main St',
        ],
    ]);

    expect($orders)->toHaveCount(1);
    expect($orders[0]->store_id)->toBe($store->id);
    expect($orders[0]->total)->toBe(900); // (2*100 + 1*200) + 500 delivery = 400 + 500
    expect(OrderItem::query()->where('order_id', $orders[0]->id)->count())->toBe(2);
});

test('place_order creates separate orders for different stores', function () {
    $customer = Customer::factory()->create();
    $store1 = Store::factory()->create(['type' => StoreType::Physical]);
    $store2 = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();

    $product1 = Product::factory()->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);
    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    $city1 = City::factory()->create();
    $city2 = City::factory()->create();
    $store1->cities()->attach($city1->id, ['price' => 500]);
    $store2->cities()->attach($city2->id, ['price' => 300]);

    $service = app(OrderService::class);

    $orders = $service->place_order($customer, [
        $store1->id => [
            'city_id' => $city1->id,
            'address' => '123 Main St',
        ],
        $store2->id => [
            'city_id' => $city2->id,
            'address' => '456 Oak Ave',
        ],
    ]);

    expect($orders)->toHaveCount(2);
    expect($orders[0]->store_id)->toBe($store1->id);
    expect($orders[1]->store_id)->toBe($store2->id);
});

test('place_order validates physical stores require address and city', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = app(OrderService::class);

    expect(fn () => $service->place_order($customer, []))
        ->toThrow(\InvalidArgumentException::class, 'Address and city are required');
});

test('place_order validates digital stores do not require address', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = app(OrderService::class);

    $orders = $service->place_order($customer);

    expect($orders)->toHaveCount(1);
    expect($orders[0]->city_id)->toBeNull();
    expect($orders[0]->address)->toBeNull();
    expect($orders[0]->delivery_price)->toBe(0);
});

test('place_order calculates delivery price correctly for physical stores', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    $city = City::factory()->create();
    // Ensure clean state - detach first if exists
    $store->cities()->detach($city->id);
    $store->cities()->attach($city->id, ['price' => 5000]);

    $service = app(OrderService::class);

    $orders = $service->place_order($customer, [
        $store->id => [
            'city_id' => $city->id,
            'address' => '123 Main St',
        ],
    ]);

    expect($orders[0]->delivery_price)->toBe(5000);
    // Items total: 1000 (1 item * 1000), Delivery: 5000, Total should be 6000
    expect($orders[0]->total)->toBe(6000); // 1000 + 5000
});

test('place_order sets delivery price to 0 for digital stores', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    $service = app(OrderService::class);

    $orders = $service->place_order($customer);

    expect($orders[0]->delivery_price)->toBe(0);
    expect($orders[0]->total)->toBe(1000);
});

test('place_order clears cart after order placement', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $city = City::factory()->create();
    $store->cities()->attach($city->id, ['price' => 500]);

    $service = app(OrderService::class);

    $service->place_order($customer, [
        $store->id => [
            'city_id' => $city->id,
            'address' => '123 Main St',
        ],
    ]);

    expect(CartItem::query()->find($cart_item->id))->toBeNull();
});

test('place_order validates products are active', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = app(OrderService::class);

    expect(fn () => $service->place_order($customer))
        ->toThrow(\InvalidArgumentException::class, 'not available');
});

test('place_order validates stock availability', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 5,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $service = app(OrderService::class);

    expect(fn () => $service->place_order($customer))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient stock');
});

test('place_order uses transaction rollback on error', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 5,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 10, // More than stock
    ]);

    $city = City::factory()->create();
    $store->cities()->attach($city->id, ['price' => 500]);

    $service = app(OrderService::class);

    try {
        $service->place_order($customer, [
            $store->id => [
                'city_id' => $city->id,
                'address' => '123 Main St',
            ],
        ]);
    } catch (\Exception $e) {
        // Expected to throw
    }

    // Verify no orders were created
    expect(Order::query()->where('customer_id', $customer->id)->count())->toBe(0);
    expect(OrderItem::query()->count())->toBe(0);
});

test('calculate_delivery_price returns correct price from pivot', function () {
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $city = City::factory()->create();

    DB::table('city_store_delivery')->insert([
        'store_id' => $store->id,
        'city_id' => $city->id,
        'price' => 7500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(OrderService::class);

    $price = $service->calculate_delivery_price($store, $city->id);

    expect($price)->toBe(7500);
});

test('calculate_delivery_price returns 0 for digital stores', function () {
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $city = City::factory()->create();

    $service = app(OrderService::class);

    $price = $service->calculate_delivery_price($store, $city->id);

    expect($price)->toBe(0);
});

test('get_customer_orders returns paginated orders', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Order::factory()->count(25)->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
    ]);

    $service = app(OrderService::class);

    $result = $service->get_customer_orders($customer, 10, 1);

    expect($result)->toHaveKeys(['data', 'paginator']);
    expect($result['data'])->toHaveCount(10);
    expect($result['paginator']->total())->toBe(25);
});

test('get_order returns order details', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    $service = app(OrderService::class);

    $order_data = $service->get_order($customer, $order->id);

    expect($order_data)->toBeArray();
    expect($order_data['id'])->toBe($order->id);
    expect($order_data['items'])->toHaveCount(1);
    expect($order_data['items'][0]['quantity'])->toBe(2);
    expect($order_data['items'][0]['price'])->toBe(1000);
});

test('get_order only returns customer own orders', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $store = Store::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer1->id,
        'store_id' => $store->id,
    ]);

    $service = app(OrderService::class);

    $order_data = $service->get_order($customer2, $order->id);

    expect($order_data)->toBeNull();
});

test('place_order updates product stock', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $service = app(OrderService::class);

    $service->place_order($customer);

    $product->refresh();

    expect($product->stock)->toBe(7);
});

test('place_order throws exception for empty cart', function () {
    $customer = Customer::factory()->create();

    $service = app(OrderService::class);

    expect(fn () => $service->place_order($customer))
        ->toThrow(\InvalidArgumentException::class, 'Cart is empty');
});

test('place_order validates address cannot be empty for physical stores', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $city = City::factory()->create();
    $store->cities()->attach($city->id, ['price' => 500]);

    $service = app(OrderService::class);

    expect(fn () => $service->place_order($customer, [
        $store->id => [
            'city_id' => $city->id,
            'address' => '', // Empty address
        ],
    ]))->toThrow(\InvalidArgumentException::class, 'Address cannot be empty');
});

