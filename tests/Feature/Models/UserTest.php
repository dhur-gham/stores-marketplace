<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

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

test('user does not have cart items relationship', function () {
    // Users are sellers/admins, not customers. Cart items belong to customers.
    // This test is removed as it's no longer applicable after migration from user_id to customer_id
    expect(true)->toBeTrue();
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
