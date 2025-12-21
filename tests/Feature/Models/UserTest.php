<?php

use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Order;

test('can create a user', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->exists)->toBeTrue();
});

test('user has stores relationship', function () {
    $user = User::factory()->create();
    $stores = Store::factory()->count(3)->create();

    $user->stores()->attach($stores->pluck('id'));

    expect($user->stores)->toHaveCount(3)
        ->and($user->stores->first())->toBeInstanceOf(Store::class);
});

test('user has products relationship', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();
    
    $products = Product::factory()->count(2)->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
    ]);

    expect($user->products)->toHaveCount(2)
        ->and($user->products->first())->toBeInstanceOf(Product::class);
});

test('user has cart items relationship', function () {
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

    expect($user->cart_items)->toHaveCount(1)
        ->and($user->cart_items->first())->toBeInstanceOf(CartItem::class);
});

test('user has orders relationship', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    Order::factory()->count(2)->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
    ]);

    expect($user->orders)->toHaveCount(2)
        ->and($user->orders->first())->toBeInstanceOf(Order::class);
});

test('user initials method returns correct initials', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    expect($user->initials())->toBe('JD');
});

test('user initials method works with single name', function () {
    $user = User::factory()->create(['name' => 'John']);

    expect($user->initials())->toBe('J');
});

test('user can access filament panel', function () {
    $user = User::factory()->create();
    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});
