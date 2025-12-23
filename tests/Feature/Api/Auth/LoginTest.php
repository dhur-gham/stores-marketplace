<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

test('customer can login with valid credentials', function () {
    $customer = Customer::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'customer' => ['id', 'name', 'email'],
            'token',
        ]);
});

test('login fails with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login fails with invalid password', function () {
    $customer = Customer::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('customer can logout', function () {
    $customer = Customer::factory()->create();
    $token = $customer->createToken('auth-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson('/api/v1/auth/logout');

    $response->assertSuccessful()
        ->assertJson(['message' => 'Logged out successfully']);

    expect($customer->tokens()->count())->toBe(0);
});
