<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Http;

test('webhook handles start command with valid customer id', function () {
    $customer = Customer::factory()->create();

    $webhook_data = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 959599690,
                'is_bot' => false,
                'first_name' => 'Test',
                'username' => 'testuser',
            ],
            'chat' => [
                'id' => 959599690,
                'first_name' => 'Test',
                'username' => 'testuser',
                'type' => 'private',
            ],
            'date' => time(),
            'text' => "/start cust-{$customer->id}",
        ],
    ];

    // Mock Telegram API response for sending confirmation message
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'message_id' => 1,
                'chat' => ['id' => 959599690],
            ],
        ], 200),
    ]);

    $response = $this->postJson('/api/v1/telegram/webhook', $webhook_data);

    $response->assertSuccessful()
        ->assertJson(['ok' => true]);

    // Verify customer chat_id was updated
    $customer->refresh();
    expect($customer->telegram_chat_id)->toBe(959599690);
});

test('webhook handles start command without customer id', function () {
    $webhook_data = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 959599690,
                'is_bot' => false,
                'first_name' => 'Test',
            ],
            'chat' => [
                'id' => 959599690,
                'first_name' => 'Test',
                'type' => 'private',
            ],
            'date' => time(),
            'text' => '/start',
        ],
    ];

    // Mock Telegram API response
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ], 200),
    ]);

    $response = $this->postJson('/api/v1/telegram/webhook', $webhook_data);

    $response->assertSuccessful()
        ->assertJson(['ok' => true]);
});

test('webhook handles invalid customer id', function () {
    $webhook_data = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 959599690,
                'is_bot' => false,
                'first_name' => 'Test',
            ],
            'chat' => [
                'id' => 959599690,
                'first_name' => 'Test',
                'type' => 'private',
            ],
            'date' => time(),
            'text' => '/start cust-99999',
        ],
    ];

    // Mock Telegram API response
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ], 200),
    ]);

    $response = $this->postJson('/api/v1/telegram/webhook', $webhook_data);

    $response->assertSuccessful()
        ->assertJson(['ok' => true]);
});

test('webhook ignores non-start messages', function () {
    $webhook_data = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 959599690,
                'is_bot' => false,
            ],
            'chat' => [
                'id' => 959599690,
                'type' => 'private',
            ],
            'date' => time(),
            'text' => 'Hello bot',
        ],
    ];

    $response = $this->postJson('/api/v1/telegram/webhook', $webhook_data);

    $response->assertSuccessful()
        ->assertJson(['ok' => true]);
});
