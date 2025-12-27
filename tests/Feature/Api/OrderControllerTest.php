<?php

use App\Enums\ProductStatus;
use App\Enums\StoreType;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('unauthenticated user cannot access order endpoints', function () {
    $response = $this->getJson('/api/v1/orders');

    $response->assertUnauthorized();
});

test('authenticated user can place order', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
        'price' => 1000,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', []);

    $response->assertSuccessful()
        ->assertJsonPath('data.orders', fn ($orders) => count($orders) === 1);
});

test('place order validates address for physical stores', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
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

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', []);

    $response->assertStatus(422);
});

test('place order validates city_id for physical stores', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
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

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'address_data' => [
                $store->id => [
                    'address' => '123 Main St',
                    // Missing city_id
                ],
            ],
        ]);

    $response->assertStatus(422);
});

test('place order does not require address for digital stores', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create(['type' => StoreType::Digital]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
        'price' => 1000,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', []);

    $response->assertSuccessful();
});

test('place order creates multiple orders for multiple stores', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store1 = Store::factory()->create(['type' => StoreType::Physical]);
    $store2 = Store::factory()->create(['type' => StoreType::Digital]);
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

    $city = City::factory()->create();
    $store1->cities()->attach($city->id, ['price' => 500]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'address_data' => [
                $store1->id => [
                    'city_id' => $city->id,
                    'address' => '123 Main St',
                ],
            ],
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.orders', fn ($orders) => count($orders) === 2);
});

test('authenticated user can get orders list', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();

    Order::factory()->count(5)->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                0 => [
                    'id',
                    'store',
                    'total',
                    'status',
                ],
            ],
            'meta' => [
                'current_page',
                'total',
            ],
        ]);
});

test('authenticated user can get order details', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
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

    \App\Models\OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'store',
                'items',
                'total',
                'status',
            ],
        ]);
});

test('customer can only view their own orders', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $token1 = $customer1->createToken('test-token')->plainTextToken;
    $token2 = $customer2->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer1->id,
        'store_id' => $store->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token2}")
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertNotFound();
});

test('order returns correct structure with items and totals', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create(['type' => StoreType::Physical]);
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
        'price' => 1000,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    $city = City::factory()->create();
    $store->cities()->attach($city->id, ['price' => 500]);

    $place_order_response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'address_data' => [
                $store->id => [
                    'city_id' => $city->id,
                    'address' => '123 Main St',
                ],
            ],
        ]);

    $place_order_response->assertSuccessful();
    $order_id = $place_order_response->json('data.orders.0.id');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order_id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'store',
                'items' => [
                    0 => [
                        'id',
                        'product',
                        'quantity',
                        'price',
                        'subtotal',
                    ],
                ],
                'items_total',
                'delivery_price',
                'total',
                'status',
            ],
        ])
        ->assertJsonPath('data.items_total', 2000) // 2 * 1000
        ->assertJsonPath('data.delivery_price', 500)
        ->assertJsonPath('data.total', 2500); // 2000 + 500
});
