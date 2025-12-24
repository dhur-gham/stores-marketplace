<?php

use App\Models\Customer;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

test('telegram service sends message successfully', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'message_id' => 1,
                'chat' => ['id' => 959599690],
            ],
        ], 200),
    ]);

    $service = new TelegramService;
    $result = $service->sendMessage(959599690, 'Test message');

    expect($result)->not->toBeNull()
        ->and($result['ok'])->toBeTrue();
});

test('telegram service sends message to customer', function () {
    $customer = Customer::factory()->create([
        'telegram_chat_id' => 959599690,
    ]);

    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'message_id' => 1,
                'chat' => ['id' => 959599690],
            ],
        ], 200),
    ]);

    $service = new TelegramService;
    $result = $service->sendMessageToCustomer($customer, 'Test message');

    expect($result)->not->toBeNull()
        ->and($result['ok'])->toBeTrue();
});

test('telegram service returns null when customer has no chat_id', function () {
    $customer = Customer::factory()->create([
        'telegram_chat_id' => null,
    ]);

    $service = new TelegramService;
    $result = $service->sendMessageToCustomer($customer, 'Test message');

    expect($result)->toBeNull();
});

test('telegram service formats message correctly', function () {
    $service = new TelegramService;

    $message = $service->formatMessage(
        'Order Confirmed',
        'Your order has been confirmed.',
        ['Order ID' => '12345', 'Total' => '100 IQD']
    );

    expect($message)->toContain('<b>Order Confirmed</b>')
        ->and($message)->toContain('Your order has been confirmed.')
        ->and($message)->toContain('<b>Order ID:</b> 12345')
        ->and($message)->toContain('<b>Total:</b> 100 IQD');
});
