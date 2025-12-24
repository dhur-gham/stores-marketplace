<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WishlistItem;

test('unauthenticated user cannot access wishlist endpoints', function () {
    $response = $this->getJson('/api/v1/wishlist');

    $response->assertUnauthorized();
});

test('authenticated user can get wishlist', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wishlist');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'items',
                'count',
            ],
        ]);
});

test('authenticated user can add to wishlist', function () {
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
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->id,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 1);
});

test('add to wishlist validates product_id', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist', [
            'product_id' => 99999,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('add to wishlist validates product is active', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'inactive',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->id,
        ]);

    $response->assertUnprocessable();
});

test('adding same product twice does not create duplicate', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->id,
        ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->id,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 1);

    expect(WishlistItem::where('customer_id', $customer->id)->where('product_id', $product->id)->count())->toBe(1);
});

test('authenticated user can remove from wishlist', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $wishlist_item = WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/wishlist/{$wishlist_item->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.count', 0);
});

test('authenticated user can check wishlist status', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/wishlist/check/{$product->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.in_wishlist', true)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'in_wishlist',
                'wishlist_item_id',
            ],
        ]);
});

test('check wishlist returns false for product not in wishlist', function () {
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
        ->getJson("/api/v1/wishlist/check/{$product->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.in_wishlist', false)
        ->assertJsonPath('data.wishlist_item_id', null);
});

test('customer can only modify their own wishlist items', function () {
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
    ]);

    $wishlist_item = WishlistItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token2}")
        ->deleteJson("/api/v1/wishlist/{$wishlist_item->id}");

    $response->assertNotFound();
});

test('wishlist returns correct structure with items', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wishlist');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'items' => [
                    0 => [
                        'id',
                        'product_id',
                        'product' => [
                            'id',
                            'name',
                            'slug',
                            'image',
                            'price',
                            'stock',
                            'status',
                            'store' => [
                                'id',
                                'name',
                                'slug',
                                'type',
                            ],
                        ],
                    ],
                ],
                'count',
            ],
        ])
        ->assertJsonPath('data.count', 1);
});
