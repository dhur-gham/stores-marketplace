<?php

use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Store;

test('can create a cart item', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50.00,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 50.00,
    ]);

    expect($cart_item->quantity)->toBe(2)
        ->and($cart_item->price)->toBe('50.00')
        ->and($cart_item->exists)->toBeTrue();
});

test('cart item belongs to user', function () {
    $user = User::factory()->create(['name' => 'Cart Owner']);
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item->user)->toBeInstanceOf(User::class)
        ->and($cart_item->user->name)->toBe('Cart Owner')
        ->and($cart_item->user_id)->toBe($user->id);
});

test('cart item belongs to product', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Cart Product',
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item->product)->toBeInstanceOf(Product::class)
        ->and($cart_item->product->name)->toBe('Cart Product')
        ->and($cart_item->product_id)->toBe($product->id);
});

test('cart item quantity is cast to integer', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => '5',
    ]);

    expect($cart_item->quantity)->toBeInt()
        ->and($cart_item->quantity)->toBe(5);
});

test('cart item price is cast to decimal', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'price' => 99.999,
    ]);

    expect($cart_item->price)->toBe('100.00');
});

test('cart item requires user_id', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect(fn () => CartItem::factory()->create([
        'user_id' => null,
        'product_id' => $product->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('cart item requires product_id', function () {
    $user = User::factory()->create();

    expect(fn () => CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => null,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('user cannot have duplicate cart items for same product', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    expect(fn () => CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('different users can have cart items for same product', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user1->id,
    ]);

    $cart_item1 = CartItem::factory()->create([
        'user_id' => $user1->id,
        'product_id' => $product->id,
    ]);

    $cart_item2 = CartItem::factory()->create([
        'user_id' => $user2->id,
        'product_id' => $product->id,
    ]);

    expect($cart_item1->product_id)->toBe($product->id)
        ->and($cart_item2->product_id)->toBe($product->id)
        ->and($cart_item1->user_id)->not->toBe($cart_item2->user_id);
});

test('user can have multiple cart items for different products', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product1 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);
    $product2 = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item1 = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product1->id,
    ]);

    $cart_item2 = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product2->id,
    ]);

    expect($cart_item1->user_id)->toBe($user->id)
        ->and($cart_item2->user_id)->toBe($user->id)
        ->and($user->cart_items)->toHaveCount(2);
});

test('cart item price is always formatted to 2 decimal places', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'price' => 123.456,
    ]);

    expect($cart_item->price)->toBe('123.46');
});

test('cart item can have high quantity', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 999,
    ]);

    expect($cart_item->quantity)->toBe(999)
        ->and($cart_item->exists)->toBeTrue();
});

test('multiple cart items can reference same product', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user1->id,
    ]);

    CartItem::factory()->create([
        'user_id' => $user1->id,
        'product_id' => $product->id,
    ]);

    CartItem::factory()->create([
        'user_id' => $user2->id,
        'product_id' => $product->id,
    ]);

    CartItem::factory()->create([
        'user_id' => $user3->id,
        'product_id' => $product->id,
    ]);

    expect($product->cart_items)->toHaveCount(3);
});

test('cart item can update quantity', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    expect($cart_item->quantity)->toBe(1);

    $cart_item->update(['quantity' => 5]);

    expect($cart_item->quantity)->toBe(5);
});

test('cart item can update price', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $cart_item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'price' => 50.00,
    ]);

    expect($cart_item->price)->toBe('50.00');

    $cart_item->update(['price' => 75.00]);

    expect($cart_item->price)->toBe('75.00');
});
