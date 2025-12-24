<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WishlistItem;

test('wishlist item belongs to customer', function () {
    $customer = Customer::factory()->create();
    $wishlist_item = WishlistItem::factory()->create([
        'customer_id' => $customer->id,
    ]);

    expect($wishlist_item->customer)->toBeInstanceOf(Customer::class)
        ->and($wishlist_item->customer->id)->toBe($customer->id);
});

test('wishlist item belongs to product', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);
    $wishlist_item = WishlistItem::factory()->create([
        'product_id' => $product->id,
    ]);

    expect($wishlist_item->product)->toBeInstanceOf(Product::class)
        ->and($wishlist_item->product->id)->toBe($product->id);
});

test('customer has many wishlist items', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);
    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    expect($customer->wishlist_items)->toHaveCount(2);
});

test('product has many wishlist items', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    WishlistItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);
    WishlistItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product->id,
    ]);

    expect($product->wishlist_items)->toHaveCount(2);
});

test('wishlist item factory creates valid wishlist item', function () {
    $wishlist_item = WishlistItem::factory()->create();

    expect($wishlist_item)->toBeInstanceOf(WishlistItem::class)
        ->and($wishlist_item->customer_id)->toBeInt()
        ->and($wishlist_item->product_id)->toBeInt();
});

test('wishlist item has unique constraint on customer and product', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    expect(function () use ($customer, $product) {
        WishlistItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});
