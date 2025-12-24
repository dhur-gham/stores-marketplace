<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WishlistItem;
use App\Models\WishlistShare;

test('unauthenticated user cannot access share endpoints', function () {
    $response = $this->getJson('/api/v1/wishlist/share');

    $response->assertUnauthorized();
});

test('authenticated user can generate share link', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist/share', [
            'custom_message' => 'Check out my wishlist!',
        ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'share_token',
                'share_url',
                'custom_message',
                'is_active',
                'views_count',
            ],
        ])
        ->assertJsonPath('data.custom_message', 'Check out my wishlist!')
        ->assertJsonPath('data.is_active', true);

    expect(WishlistShare::where('customer_id', $customer->id)->exists())->toBeTrue();
});

test('authenticated user can get existing share link', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'custom_message' => 'My wishlist',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wishlist/share');

    $response->assertSuccessful()
        ->assertJsonPath('data.share_token', $wishlist_share->share_token)
        ->assertJsonPath('data.custom_message', 'My wishlist');
});

test('public can view shared wishlist with valid token', function () {
    $customer = Customer::factory()->create();
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

    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/v1/wishlist/share/{$wishlist_share->share_token}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'share' => [
                    'id',
                    'share_token',
                    'custom_message',
                    'views_count',
                ],
                'customer' => [
                    'id',
                    'name',
                ],
                'wishlist_items',
            ],
        ]);

    expect($wishlist_share->fresh()->views_count)->toBe(1);
});

test('public cannot view shared wishlist with invalid token', function () {
    $response = $this->getJson('/api/v1/wishlist/share/invalid-token');

    $response->assertNotFound();
});

test('public cannot view inactive shared wishlist', function () {
    $customer = Customer::factory()->create();
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'is_active' => false,
    ]);

    $response = $this->getJson("/api/v1/wishlist/share/{$wishlist_share->share_token}");

    $response->assertNotFound();
});

test('viewing shared wishlist increments view count', function () {
    $customer = Customer::factory()->create();
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'is_active' => true,
        'views_count' => 5,
    ]);

    $this->getJson("/api/v1/wishlist/share/{$wishlist_share->share_token}");

    expect($wishlist_share->fresh()->views_count)->toBe(6);
});

test('authenticated user can update share message', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/message', [
            'custom_message' => 'Updated message!',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.custom_message', 'Updated message!');

    expect($wishlist_share->fresh()->custom_message)->toBe('Updated message!');
});

test('update share message validates custom_message', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/message', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['custom_message']);
});

test('update share message creates share link if none exists', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/message', [
            'custom_message' => 'New share link!',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.custom_message', 'New share link!');

    expect(WishlistShare::where('customer_id', $customer->id)->exists())->toBeTrue();
});

test('authenticated user can toggle share active status', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/toggle', [
            'is_active' => false,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.is_active', false);

    expect($wishlist_share->fresh()->is_active)->toBeFalse();
});

test('toggle share validates is_active', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/toggle', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['is_active']);
});

test('toggle share creates share link if activating and none exists', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/toggle', [
            'is_active' => true,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.is_active', true);

    expect(WishlistShare::where('customer_id', $customer->id)->exists())->toBeTrue();
});

test('toggle share returns error if deactivating and no share exists', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/wishlist/share/toggle', [
            'is_active' => false,
        ]);

    $response->assertNotFound();
});

test('one share link per customer', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('test-token')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist/share');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wishlist/share');

    expect(WishlistShare::where('customer_id', $customer->id)->count())->toBe(1);
});

test('share link includes wishlist items', function () {
    $customer = Customer::factory()->create();
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

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);
    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/v1/wishlist/share/{$wishlist_share->share_token}");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data.wishlist_items');
});
