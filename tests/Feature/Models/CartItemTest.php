<?php

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('can create a cart item', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50.00,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 50,
    ]);

    expect($cart_item->quantity)->toBe(2)
        ->and($cart_item->price)->toBe(50)
        ->and($cart_item->exists)->toBeTrue();
});

test('cart item belongs to customer', function () {
    $customer = Customer::factory()->create(['name' => 'Cart Owner']);
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item->customer)->toBeInstanceOf(Customer::class)
        ->and($cart_item->customer->name)->toBe('Cart Owner')
        ->and($cart_item->customer_id)->toBe($customer->id);
});

test('cart item belongs to product', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Cart Product',
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item->product)->toBeInstanceOf(Product::class)
        ->and($cart_item->product->name)->toBe('Cart Product')
        ->and($cart_item->product_id)->toBe($product->id);
});

test('cart item quantity is cast to integer', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => '5',
    ]);

    expect($cart_item->quantity)->toBeInt()
        ->and($cart_item->quantity)->toBe(5);
});

test('cart item price is stored correctly', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'price' => 100,
    ]);

    expect($cart_item->price)->toBe(100);
});

test('cart item requires customer_id', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect(fn () => CartItem::factory()->create([
        'customer_id' => null,
        'product_id' => $product->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('cart item requires product_id', function () {
    $customer = Customer::factory()->create();

    expect(fn () => CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => null,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('customer cannot have duplicate cart items for same product', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    expect(fn () => CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('different customers can have cart items for same product', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item1 = CartItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    $cart_item2 = CartItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item1->product_id)->toBe($product->id)
        ->and($cart_item2->product_id)->toBe($product->id)
        ->and($cart_item1->customer_id)->not->toBe($cart_item2->customer_id);
});

test('customer can have multiple cart items for different products', function () {
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

    $cart_item1 = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product1->id,
    ]);

    $cart_item2 = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product2->id,
    ]);

    expect($cart_item1->customer_id)->toBe($customer->id)
        ->and($cart_item2->customer_id)->toBe($customer->id)
        ->and($customer->cart_items)->toHaveCount(2);
});

test('cart item price is cast to integer', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'price' => 123,
    ]);

    expect($cart_item->price)->toBe(123);
});

test('cart item can have high quantity', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 999,
    ]);

    expect($cart_item->quantity)->toBe(999)
        ->and($cart_item->exists)->toBeTrue();
});

test('multiple cart items can reference same product', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $customer3 = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer1->id,
        'product_id' => $product->id,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer2->id,
        'product_id' => $product->id,
    ]);

    CartItem::factory()->create([
        'customer_id' => $customer3->id,
        'product_id' => $product->id,
    ]);

    expect($product->cart_items)->toHaveCount(3);
});

test('cart item can update quantity', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    expect($cart_item->quantity)->toBe(1);

    $cart_item->update(['quantity' => 5]);

    expect($cart_item->quantity)->toBe(5);
});

test('cart item can update price', function () {
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'price' => 50,
    ]);

    expect($cart_item->price)->toBe(50);

    $cart_item->update(['price' => 75]);

    expect($cart_item->price)->toBe(75);
});
