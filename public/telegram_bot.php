<?php

/**
 * Telegram Bot Webhook Handler
 *
 * This file handles incoming webhook updates from Telegram.
 * It processes /start commands with customer IDs and updates the Laravel database.
 *
 * Deploy this file to: https://yourdomain.com/telegram_bot.php
 * Set webhook URL in Telegram: https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yourdomain.com/telegram_bot.php
 */

// Load Laravel application
// Auto-detect Laravel root: go up until we find vendor/autoload.php
$laravel_root = __DIR__;
$max_levels = 5; // Prevent infinite loop
$level = 0;

while (! file_exists($laravel_root.'/vendor/autoload.php') && $laravel_root !== '/' && $level < $max_levels) {
    $laravel_root = dirname($laravel_root);
    $level++;
}

if (! file_exists($laravel_root.'/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Laravel application not found', 'searched_path' => $laravel_root]);
    exit;
}

require $laravel_root.'/vendor/autoload.php';

$app = require_once $laravel_root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Services\TelegramService;

// Get bot token from config
$bot_token = config('services.telegram.bot_token', env('BOT_TOKEN'));

if (empty($bot_token)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Bot token not configured']);
    exit;
}

// Get webhook update
$update = json_decode(file_get_contents('php://input'), true);

if (! isset($update['message'])) {
    // Not a message update, acknowledge and exit
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

$message = $update['message'];
$chat_id = $message['chat']['id'] ?? null;
$from = $message['from'] ?? [];

if (! $chat_id) {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

// Initialize Telegram service
$telegram_service = new TelegramService;

// Handle /start command
if (isset($message['text']) && str_starts_with($message['text'], '/start')) {
    $text = $message['text'];

    // Extract parameter from /start cust-123
    // Handle both formats: /start cust-123 and /startcust-123
    $start_param = substr($text, 6); // Remove "/start" (6 characters)
    $start_param = ltrim($start_param, ' '); // Remove leading space if exists

    if (empty($start_param) || ! str_starts_with($start_param, 'cust-')) {
        // No customer ID provided, send generic welcome message
        $welcome_message = "👋 Welcome!\n\n";
        $welcome_message .= 'To activate Telegram notifications, please use the activation link from your account settings.';

        $telegram_service->sendMessage($chat_id, $welcome_message);

        http_response_code(200);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Extract customer ID from "cust-123" -> 123
    $customer_id = (int) str_replace('cust-', '', $start_param);

    if ($customer_id <= 0) {
        // Invalid customer ID
        $error_message = "❌ Sorry, we couldn't activate your Telegram notifications.\n\n";
        $error_message .= "Please make sure you're using the correct activation link from your account.";

        $telegram_service->sendMessage($chat_id, $error_message);

        http_response_code(200);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Find customer
    try {
        $customer = Customer::find($customer_id);

        if (! $customer) {
            // Customer not found
            $error_message = "❌ Sorry, we couldn't activate your Telegram notifications.\n\n";
            $error_message .= "Please make sure you're using the correct activation link from your account.";

            $telegram_service->sendMessage($chat_id, $error_message);

            http_response_code(200);
            echo json_encode(['ok' => true]);
            exit;
        }

        // Update customer with chat_id
        $customer->telegram_chat_id = $chat_id;
        $customer->save();

        // Send confirmation message
        $confirmation_message = "✅ <b>Telegram Notifications Activated!</b>\n\n";
        $confirmation_message .= "Hello {$customer->name},\n\n";
        $confirmation_message .= "Your Telegram account has been successfully linked to your store account.\n";
        $confirmation_message .= 'You will now receive notifications about your orders and important updates.';

        $telegram_service->sendMessage($chat_id, $confirmation_message);

    } catch (\Exception $e) {
        // Log error and send generic error message
        \Illuminate\Support\Facades\Log::error('Telegram webhook error', [
            'error' => $e->getMessage(),
            'customer_id' => $customer_id,
            'chat_id' => $chat_id,
        ]);

        $error_message = "❌ Sorry, an error occurred while activating your Telegram notifications.\n\n";
        $error_message .= 'Please try again later or contact support.';

        $telegram_service->sendMessage($chat_id, $error_message);
    }
}

// Always return 200 OK to acknowledge receipt
http_response_code(200);
echo json_encode(['ok' => true]);
