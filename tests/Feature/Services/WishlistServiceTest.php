<?php

use App\Enums\ProductStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WishlistItem;
use App\Services\WishlistService;

test('get_wishlist returns empty array for customer with no wishlist items', function () {
    $customer = Customer::factory()->create();
    $service = new WishlistService;

    $wishlist = $service->get_wishlist($customer);

    expect($wishlist)->toBeArray()
        ->and($wishlist)->toBeEmpty();
});

test('get_wishlist returns wishlist items with product details', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = new WishlistService;
    $wishlist = $service->get_wishlist($customer);

    expect($wishlist)->toBeArray()
        ->and($wishlist)->toHaveCount(1)
        ->and($wishlist[0])->toHaveKeys(['id', 'product_id', 'product'])
        ->and($wishlist[0]['product'])->toHaveKeys(['id', 'name', 'slug', 'image', 'price', 'stock', 'status', 'store']);
});

test('add_to_wishlist creates new wishlist item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new WishlistService;
    $wishlist_item = $service->add_to_wishlist($customer, $product->id);

    expect($wishlist_item)->toBeInstanceOf(WishlistItem::class)
        ->and($wishlist_item->customer_id)->toBe($customer->id)
        ->and($wishlist_item->product_id)->toBe($product->id)
        ->and(WishlistItem::where('customer_id', $customer->id)->count())->toBe(1);
});

test('add_to_wishlist returns existing item if product already in wishlist', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $existing_item = WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = new WishlistService;
    $wishlist_item = $service->add_to_wishlist($customer, $product->id);

    expect($wishlist_item->id)->toBe($existing_item->id)
        ->and(WishlistItem::where('customer_id', $customer->id)->count())->toBe(1);
});

test('remove_from_wishlist deletes wishlist item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $wishlist_item = WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = new WishlistService;
    $result = $service->remove_from_wishlist($customer, $wishlist_item->id);

    expect($result)->toBeTrue()
        ->and(WishlistItem::find($wishlist_item->id))->toBeNull();
});

test('remove_from_wishlist throws exception if item not found', function () {
    $customer = Customer::factory()->create();

    $service = new WishlistService;

    expect(fn () => $service->remove_from_wishlist($customer, 99999))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('is_in_wishlist returns true if product is in wishlist', function () {
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

    $service = new WishlistService;
    $result = $service->is_in_wishlist($customer, $product->id);

    expect($result)->toBeTrue();
});

test('is_in_wishlist returns false if product is not in wishlist', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new WishlistService;
    $result = $service->is_in_wishlist($customer, $product->id);

    expect($result)->toBeFalse();
});

test('get_wishlist_count returns correct count', function () {
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
    $product3 = Product::factory()->create([
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
    WishlistItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product3->id,
    ]);

    $service = new WishlistService;
    $count = $service->get_wishlist_count($customer);

    expect($count)->toBe(3);
});

test('get_wishlist_count returns zero for empty wishlist', function () {
    $customer = Customer::factory()->create();

    $service = new WishlistService;
    $count = $service->get_wishlist_count($customer);

    expect($count)->toBe(0);
});

test('get_wishlist only returns items for specific customer', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
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
        'customer_id' => $customer1->id,
        'product_id' => $product1->id,
    ]);
    WishlistItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product2->id,
    ]);

    $service = new WishlistService;
    $wishlist1 = $service->get_wishlist($customer1);
    $wishlist2 = $service->get_wishlist($customer2);

    expect($wishlist1)->toHaveCount(1)
        ->and($wishlist2)->toHaveCount(1)
        ->and($wishlist1[0]['product_id'])->toBe($product1->id)
        ->and($wishlist2[0]['product_id'])->toBe($product2->id);
});

test('remove_from_wishlist only removes items belonging to customer', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $wishlist_item1 = WishlistItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);
    $wishlist_item2 = WishlistItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product->id,
    ]);

    $service = new WishlistService;
    $service->remove_from_wishlist($customer1, $wishlist_item1->id);

    expect(WishlistItem::find($wishlist_item1->id))->toBeNull()
        ->and(WishlistItem::find($wishlist_item2->id))->not->toBeNull();
});
