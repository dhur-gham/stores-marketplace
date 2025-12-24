<?php

use App\Models\Customer;
use App\Models\WishlistShare;

test('wishlist share belongs to customer', function () {
    $customer = Customer::factory()->create();
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
    ]);

    expect($wishlist_share->customer)->toBeInstanceOf(Customer::class)
        ->and($wishlist_share->customer->id)->toBe($customer->id);
});

test('customer has one wishlist share', function () {
    $customer = Customer::factory()->create();
    $wishlist_share = WishlistShare::factory()->create([
        'customer_id' => $customer->id,
    ]);

    expect($customer->wishlist_share)->toBeInstanceOf(WishlistShare::class)
        ->and($customer->wishlist_share->id)->toBe($wishlist_share->id);
});

test('wishlist share generates unique token', function () {
    $token1 = WishlistShare::generate_token();
    $token2 = WishlistShare::generate_token();

    expect($token1)->not->toBe($token2)
        ->and(strlen($token1))->toBe(32)
        ->and(strlen($token2))->toBe(32);
});

test('wishlist share factory creates valid share', function () {
    $wishlist_share = WishlistShare::factory()->create();

    expect($wishlist_share->customer_id)->toBeInt()
        ->and($wishlist_share->share_token)->toBeString()
        ->and(strlen($wishlist_share->share_token))->toBe(32)
        ->and($wishlist_share->is_active)->toBeTrue()
        ->and($wishlist_share->views_count)->toBe(0);
});

test('wishlist share can increment views', function () {
    $wishlist_share = WishlistShare::factory()->create([
        'views_count' => 5,
    ]);

    $wishlist_share->increment_views();

    expect($wishlist_share->fresh()->views_count)->toBe(6);
});

test('wishlist share casts is_active to boolean', function () {
    $wishlist_share = WishlistShare::factory()->create([
        'is_active' => true,
    ]);

    expect($wishlist_share->is_active)->toBeTrue()
        ->and(is_bool($wishlist_share->is_active))->toBeTrue();
});

test('wishlist share casts views_count to integer', function () {
    $wishlist_share = WishlistShare::factory()->create([
        'views_count' => 10,
    ]);

    expect($wishlist_share->views_count)->toBe(10)
        ->and(is_int($wishlist_share->views_count))->toBeTrue();
});

test('wishlist share can have custom message', function () {
    $wishlist_share = WishlistShare::factory()->create([
        'custom_message' => 'Check out my wishlist!',
    ]);

    expect($wishlist_share->custom_message)->toBe('Check out my wishlist!');
});

test('wishlist share can have null custom message', function () {
    $wishlist_share = WishlistShare::factory()->create([
        'custom_message' => null,
    ]);

    expect($wishlist_share->custom_message)->toBeNull();
});

test('wishlist share token is unique', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    $share1 = WishlistShare::factory()->create([
        'customer_id' => $customer1->id,
    ]);

    $share2 = WishlistShare::factory()->create([
        'customer_id' => $customer2->id,
    ]);

    expect($share1->share_token)->not->toBe($share2->share_token);
});
