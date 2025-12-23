<?php

use App\Models\Customer;

test('authenticated customer can get their profile', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('auth-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJson([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/v1/auth/user');

    $response->assertUnauthorized();
});

test('user endpoint returns correct customer data', function () {
    $customer = Customer::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    $token = $customer->createToken('auth-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJsonPath('name', 'John Doe')
        ->assertJsonPath('email', 'john@example.com');
});
