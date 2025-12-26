<?php

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('unauthenticated user cannot access cart endpoints', function () {
    $response = $this->getJson('/api/v1/cart');

    $response->assertUnauthorized();
});

test('authenticated user can get cart', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/cart');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'items',
                'total',
                'count',
            ],
        ]);
});

test('authenticated user can add to cart', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'stock' => 10,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 2)
        ->assertJsonPath('data.total', $product->price * 2);
});

test('add to cart validates product_id', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('add to cart validates quantity', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('add to cart validates product is active', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'inactive',
        'stock' => 10,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable();
});

test('add to cart validates stock availability', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'stock' => 5,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Insufficient stock available');
});

test('authenticated user can update cart item', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'stock' => 10,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/{$cart_item->id}", [
            'quantity' => 5,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 5);
});

test('update validates quantity', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'stock' => 10,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/{$cart_item->id}", [
            'quantity' => 0,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('authenticated user can remove from cart', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/cart/{$cart_item->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 0);
});

test('authenticated user can clear cart', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);
    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/cart');

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 0)
        ->assertJsonPath('data.total', 0);
});

test('customer can only modify their own cart items', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $token1 = $customer1->createToken('test-token')->plainTextToken;
    $token2 = $customer2->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'stock' => 10,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token2}")
        ->putJson("/api/v1/cart/{$cart_item->id}", [
            'quantity' => 5,
        ]);

    $response->assertNotFound();

    $response = $this->withHeader('Authorization', "Bearer {$token2}")
        ->deleteJson("/api/v1/cart/{$cart_item->id}");

    $response->assertNotFound();
});

test('cart returns correct structure with totals', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
        'price' => 100,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/cart');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'items' => [
                    0 => [
                        'id',
                        'product_id',
                        'quantity',
                        'price',
                        'subtotal',
                        'product' => [
                            'id',
                            'name',
                            'slug',
                            'image',
                            'price',
                            'stock',
                            'store' => [
                                'id',
                                'name',
                                'slug',
                            ],
                        ],
                    ],
                ],
                'total',
                'count',
            ],
        ])
        ->assertJsonPath('data.total', 200)
        ->assertJsonPath('data.count', 2);
});

