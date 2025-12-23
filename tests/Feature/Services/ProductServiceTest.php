<?php

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Pagination\LengthAwarePaginator;

test('get_products_by_store returns paginator and data keys', function () {
    $store = Store::factory()->create();

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result)->toBeArray()
        ->toHaveKeys(['paginator', 'data']);
    expect($result['paginator'])->toBeInstanceOf(LengthAwarePaginator::class);
    expect($result['data'])->toBeArray();
});

test('get_products_by_store returns empty data when store has no products', function () {
    $store = Store::factory()->create();

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'])->toBeArray()->toBeEmpty();
    expect($result['paginator']->total())->toBe(0);
});

test('get_products_by_store returns all products for a store', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(5)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'])->toBeArray()->toHaveCount(5);
    expect($result['paginator']->total())->toBe(5);
});

test('get_products_by_store returns correct product structure', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'image' => 'test-image.jpg',
        'description' => 'Test description',
        'sku' => 'SKU-123',
        'status' => ProductStatus::Active,
        'type' => ProductType::Physical,
        'price' => 99,
        'stock' => 50,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0])->toMatchArray([
        'id' => $product->id,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'description' => 'Test description',
        'sku' => 'SKU-123',
        'status' => 'active',
        'type' => 'physical',
        'price' => 99,
        'stock' => 50,
    ]);
    expect($result['data'][0]['image'])->toContain('storage/test-image.jpg');
});

test('get_products_by_store only returns products for the specified store', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
    ]);

    Product::factory()->count(5)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store1);

    expect($result['data'])->toHaveCount(3);
    expect($result['paginator']->total())->toBe(3);
});

test('get_products_by_store only returns expected keys', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'][0])->toHaveKeys([
        'id',
        'name',
        'slug',
        'image',
        'description',
        'sku',
        'status',
        'type',
        'price',
        'stock',
    ]);
});

test('get_products_by_store returns products with different statuses', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Draft,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'])->toHaveCount(3);

    $statuses = collect($result['data'])->pluck('status')->toArray();
    expect($statuses)->toContain('active', 'inactive', 'draft');
});

test('get_products_by_store respects per_page parameter', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(10)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store, per_page: 5);

    expect($result['data'])->toHaveCount(5);
    expect($result['paginator']->perPage())->toBe(5);
    expect($result['paginator']->total())->toBe(10);
    expect($result['paginator']->lastPage())->toBe(2);
});

test('get_products_by_store uses default per_page of 15', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(20)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
    ]);

    $service = new ProductService;

    $result = $service->get_products_by_store($store);

    expect($result['data'])->toHaveCount(15);
    expect($result['paginator']->perPage())->toBe(15);
});

test('get_all_products returns paginator and data keys', function () {
    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result)->toBeArray()
        ->toHaveKeys(['paginator', 'data']);
    expect($result['paginator'])->toBeInstanceOf(LengthAwarePaginator::class);
    expect($result['data'])->toBeArray();
});

test('get_all_products returns empty when no products', function () {
    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result['data'])->toBeArray()->toBeEmpty();
    expect($result['paginator']->total())->toBe(0);
});

test('get_all_products returns all active products when no filters', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->count(2)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result['data'])->toHaveCount(5);
    expect($result['paginator']->total())->toBe(5);
});

test('get_all_products search filters by product name', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Tech Product',
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Fashion Product',
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(search: 'Tech');

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['name'])->toBe('Tech Product');
});

test('get_all_products filter by store_id', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(3)->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->count(2)->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(store_id: $store1->id);

    expect($result['data'])->toHaveCount(3);
    expect($result['paginator']->total())->toBe(3);
});

test('get_all_products only returns active products', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Inactive,
    ]);

    Product::factory()->count(1)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Draft,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result['data'])->toHaveCount(2);
    expect($result['paginator']->total())->toBe(2);
});

test('get_all_products filter by type', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->count(2)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => ProductType::Digital,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->count(3)->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'type' => ProductType::Physical,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(type: 'digital');

    expect($result['data'])->toHaveCount(2);
    expect($result['paginator']->total())->toBe(2);
});

test('get_all_products filter by price_min', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 200,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(price_min: 100);

    expect($result['data'])->toHaveCount(2);
});

test('get_all_products filter by price_max', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 200,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(price_max: 100);

    expect($result['data'])->toHaveCount(2);
});

test('get_all_products filter by price range', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 200,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(price_min: 75, price_max: 150);

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['price'])->toBe(100);
});

test('get_all_products sort by name ascending', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Zebra Product',
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'name' => 'Apple Product',
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(sort_by: 'name', sort_order: 'asc');

    expect($result['data'][0]['name'])->toBe('Apple Product');
    expect($result['data'][1]['name'])->toBe('Zebra Product');
});

test('get_all_products sort by price descending', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 50,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'price' => 100,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(sort_by: 'price', sort_order: 'desc');

    expect($result['data'][0]['price'])->toBe(100);
    expect($result['data'][1]['price'])->toBe(50);
});

test('get_all_products sort by created_at ascending', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $old_product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    // Wait a moment to ensure different timestamps
    sleep(1);

    $new_product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(sort_by: 'created_at', sort_order: 'asc');

    expect($result['data'][0]['id'])->toBe($old_product->id);
});

test('get_all_products default sorts by latest', function () {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $old_product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    sleep(1);

    $new_product = Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result['data'][0]['id'])->toBe($new_product->id);
});

test('get_all_products returns store information', function () {
    $store = Store::factory()->create(['name' => 'Test Store']);
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products();

    expect($result['data'][0])->toHaveKey('store');
    expect($result['data'][0]['store']['id'])->toBe($store->id);
    expect($result['data'][0]['store']['name'])->toBe('Test Store');
});

test('get_all_products multiple filters combined', function () {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $user = User::factory()->create();

    Product::factory()->create([
        'store_id' => $store1->id,
        'user_id' => $user->id,
        'name' => 'Tech Product',
        'type' => ProductType::Digital,
        'price' => 100,
        'status' => ProductStatus::Active,
    ]);

    Product::factory()->create([
        'store_id' => $store2->id,
        'user_id' => $user->id,
        'name' => 'Tech Product 2',
        'type' => ProductType::Physical,
        'price' => 150,
        'status' => ProductStatus::Active,
    ]);

    $service = new ProductService;

    $result = $service->get_all_products(
        search: 'Tech',
        store_id: $store1->id,
        type: 'digital',
        price_max: 120
    );

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['name'])->toBe('Tech Product');
});
