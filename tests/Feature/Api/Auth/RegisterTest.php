<?php

use App\Models\Customer;

test('customer can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '07717118278',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'customer' => ['id', 'name', 'email', 'phone'],
            'token',
        ]);

    expect(Customer::where('email', 'john@example.com')->exists())->toBeTrue();
});

test('registration requires name', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('registration requires valid email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration requires unique email', function () {
    Customer::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration requires password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('registration requires valid Iraqi phone number', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '1234567890',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});

test('registration accepts valid Iraqi phone number formats', function () {
    $formats = ['07717118278', '7718117187', '9647718117187'];

    foreach ($formats as $phone) {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => "john{$phone}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => $phone,
        ]);

        $response->assertSuccessful();
    }
});
