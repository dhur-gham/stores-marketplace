<?php

use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;

test('get_cart returns cart items with product details', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 100,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $service = new CartService;

    $result = $service->get_cart($customer);

    expect($result)->toBeArray()->toHaveCount(1);
    expect($result[0])->toHaveKeys(['id', 'product_id', 'quantity', 'price', 'subtotal', 'product']);
    expect($result[0]['quantity'])->toBe(2);
    expect($result[0]['price'])->toBe(100);
    expect($result[0]['subtotal'])->toBe(200);
    expect($result[0]['product']['id'])->toBe($product->id);
    expect($result[0]['product']['name'])->toBe($product->name);
});

test('get_cart returns empty array when cart is empty', function () {
    $customer = Customer::factory()->create();

    $service = new CartService;

    $result = $service->get_cart($customer);

    expect($result)->toBeArray()->toBeEmpty();
});

test('add_to_cart creates new cart item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 100,
        'stock' => 10,
    ]);

    $service = new CartService;

    $cart_item = $service->add_to_cart($customer, $product->id, 2);

    expect($cart_item)->toBeInstanceOf(CartItem::class);
    expect($cart_item->customer_id)->toBe($customer->id);
    expect($cart_item->product_id)->toBe($product->id);
    expect($cart_item->quantity)->toBe(2);
    expect($cart_item->price)->toBe(100);
});

test('add_to_cart increments quantity if product already exists', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 100,
        'stock' => 10,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $service = new CartService;

    $cart_item = $service->add_to_cart($customer, $product->id, 3);

    expect($cart_item->quantity)->toBe(5);
});

test('add_to_cart stores product price snapshot', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'price' => 100,
        'stock' => 10,
    ]);

    $service = new CartService;

    $cart_item = $service->add_to_cart($customer, $product->id, 1);

    expect($cart_item->price)->toBe(100);

    // Change product price
    $product->update(['price' => 150]);

    // Add more quantity - should use new price for new snapshot
    $cart_item2 = $service->add_to_cart($customer, $product->id, 1);

    // The existing cart item price should remain the same (snapshot)
    $cart_item->refresh();
    expect($cart_item->price)->toBe(100);
});

test('add_to_cart validates product is active', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
        'stock' => 10,
    ]);

    $service = new CartService;

    expect(fn () => $service->add_to_cart($customer, $product->id, 1))
        ->toThrow(\InvalidArgumentException::class, 'Product is not available');
});

test('add_to_cart validates stock availability', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 5,
    ]);

    $service = new CartService;

    expect(fn () => $service->add_to_cart($customer, $product->id, 10))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient stock available');
});

test('add_to_cart validates stock when incrementing existing item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
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
        'quantity' => 3,
        'price' => 100,
    ]);

    $service = new CartService;

    expect(fn () => $service->add_to_cart($customer, $product->id, 3))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient stock available');
});

test('update_cart_item updates quantity', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
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
        'quantity' => 2,
        'price' => 100,
    ]);

    $service = new CartService;

    $updated = $service->update_cart_item($customer, $cart_item->id, 5);

    expect($updated->quantity)->toBe(5);
});

test('update_cart_item validates stock availability', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 5,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $service = new CartService;

    expect(fn () => $service->update_cart_item($customer, $cart_item->id, 10))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient stock available');
});

test('update_cart_item validates quantity minimum', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
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
        'quantity' => 2,
        'price' => 100,
    ]);

    $service = new CartService;

    expect(fn () => $service->update_cart_item($customer, $cart_item->id, 0))
        ->toThrow(\InvalidArgumentException::class, 'Quantity must be at least 1');
});

test('remove_from_cart removes item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $service = new CartService;

    $result = $service->remove_from_cart($customer, $cart_item->id);

    expect($result)->toBeTrue();
    expect(CartItem::query()->find($cart_item->id))->toBeNull();
});

test('clear_cart removes all items', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);
    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    $service = new CartService;

    $result = $service->clear_cart($customer);

    expect($result)->toBeTrue();
    expect(CartItem::query()->where('customer_id', $customer->id)->count())->toBe(0);
});

test('get_cart_total calculates correct total', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
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
        'quantity' => 3,
        'price' => 50,
    ]);

    $service = new CartService;

    $total = $service->get_cart_total($customer);

    expect($total)->toBe(350); // (2 * 100) + (3 * 50)
});

test('get_cart_count returns correct count', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
        'quantity' => 3,
    ]);

    $service = new CartService;

    $count = $service->get_cart_count($customer);

    expect($count)->toBe(5); // 2 + 3
});

test('customer can only access their own cart', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product->id,
    ]);

    $service = new CartService;

    $cart1 = $service->get_cart($customer1);
    $cart2 = $service->get_cart($customer2);

    expect($cart1)->toHaveCount(1);
    expect($cart2)->toHaveCount(1);
    expect($cart1[0]['id'])->not->toBe($cart2[0]['id']);
});

test('customer can only modify their own cart items', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
        'stock' => 10,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    $service = new CartService;

    expect(fn () => $service->update_cart_item($customer2, $cart_item->id, 5))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect(fn () => $service->remove_from_cart($customer2, $cart_item->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
