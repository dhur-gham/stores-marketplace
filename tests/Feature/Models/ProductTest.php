<?php

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('can create a product', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Test Product',
        'price' => 25000,
        'stock' => 10,
    ]);

    expect($product->name)->toBe('Test Product')
        ->and($product->price)->toBe(25000)
        ->and($product->stock)->toBe(10)
        ->and($product->exists)->toBeTrue();
});

test('product belongs to store', function () {
    $store = Store::factory()->create(['name' => 'My Store']);
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect($product->store)->toBeInstanceOf(Store::class)
        ->and($product->store->name)->toBe('My Store')
        ->and($product->store_id)->toBe($store->id);
});

test('product belongs to user', function () {
    $user = User::factory()->create(['name' => 'Product Owner']);
    $store = Store::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    expect($product->user)->toBeInstanceOf(User::class)
        ->and($product->user->name)->toBe('Product Owner')
        ->and($product->user_id)->toBe($user->id);
});

test('product has cart items relationship', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    // Create cart items with different customers since there's a unique constraint on (customer_id, product_id)
    CartItem::factory()->create([
        'product_id' => $product->id,
        'customer_id' => Customer::factory()->create()->id,
    ]);

    CartItem::factory()->create([
        'product_id' => $product->id,
        'customer_id' => Customer::factory()->create()->id,
    ]);

    expect($product->cart_items)->toHaveCount(2)
        ->and($product->cart_items->first())->toBeInstanceOf(CartItem::class);
});

test('product has order items relationship', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $store = Store::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $order = \App\Models\Order::factory()->create([
        'customer_id' => $customer->id,
        'store_id' => $store->id,
    ]);

    OrderItem::factory()->count(3)->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    expect($product->order_items)->toHaveCount(3)
        ->and($product->order_items->first())->toBeInstanceOf(OrderItem::class);
});

test('product status is cast to enum', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    expect($product->status)->toBeInstanceOf(ProductStatus::class)
        ->and($product->status)->toBe(ProductStatus::Active);
});

test('product type is cast to enum', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => 'digital',
    ]);

    expect($product->type)->toBeInstanceOf(ProductType::class)
        ->and($product->type)->toBe(ProductType::Digital);
});

test('product price is cast to integer', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 25000,
    ]);

    expect($product->price)->toBeInt()
        ->and($product->price)->toBe(25000);
});

test('product stock is cast to integer', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'stock' => '50',
    ]);

    expect($product->stock)->toBeInt()
        ->and($product->stock)->toBe(50);
});

test('product slug must be unique', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'slug' => 'unique-product',
    ]);

    expect(fn () => Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'slug' => 'unique-product',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('product can have multiple statuses', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $activeProduct = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $inactiveProduct = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
    ]);

    expect($activeProduct->status)->toBe(ProductStatus::Active)
        ->and($inactiveProduct->status)->toBe(ProductStatus::Inactive);
});

test('product can have multiple types', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $digitalProduct = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => ProductType::Digital,
    ]);

    $physicalProduct = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => ProductType::Physical,
    ]);

    expect($digitalProduct->type)->toBe(ProductType::Digital)
        ->and($physicalProduct->type)->toBe(ProductType::Physical);
});

test('product price is stored as integer without decimals', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50000,
    ]);

    expect($product->price)->toBeInt()
        ->and($product->price)->toBe(50000);
});

test('product requires store_id', function () {
    $user = User::factory()->create();

    expect(fn () => Product::factory()->create([
        'store_id' => null,
        'user_id' => $user->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('product requires user_id', function () {
    $store = Store::factory()->create();

    expect(fn () => Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => null,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('product can have zero stock', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'stock' => 0,
    ]);

    expect($product->stock)->toBe(0)
        ->and($product->exists)->toBeTrue();
});

test('product can be soft deleted if trait exists', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $product_id = $product->id;

    if (method_exists($product, 'delete')) {
        $product->delete();

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($product))) {
            expect(Product::withTrashed()->find($product_id))->not->toBeNull()
                ->and(Product::find($product_id))->toBeNull();
        } else {
            expect(Product::find($product_id))->toBeNull();
        }
    }
});

test('multiple products can belong to same store', function () {
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

    expect($product1->store_id)->toBe($store->id)
        ->and($product2->store_id)->toBe($store->id)
        ->and($store->products)->toHaveCount(2);
});

test('multiple products can belong to same user', function () {
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

    expect($product1->user_id)->toBe($user->id)
        ->and($product2->user_id)->toBe($user->id)
        ->and($user->products)->toHaveCount(2);
});
