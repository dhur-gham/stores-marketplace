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
use App\Models\User;
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

    if (empty($start_param)) {
        // No ID provided, send generic welcome message
        $welcome_message = "👋 مرحباً!\n\n";
        $welcome_message .= 'لتفعيل إشعارات تيليجرام، يرجى استخدام رابط التفعيل من إعدادات حسابك.';

        $telegram_service->sendMessage($chat_id, $welcome_message);

        http_response_code(200);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Handle customer activation: cust-123
    if (str_starts_with($start_param, 'cust-')) {
        $customer_id = (int) str_replace('cust-', '', $start_param);

        if ($customer_id <= 0) {
            $error_message = "❌ عذراً، لم نتمكن من تفعيل إشعارات تيليجرام.\n\n";
            $error_message .= 'يرجى التأكد من استخدام رابط التفعيل الصحيح من حسابك.';

            $telegram_service->sendMessage($chat_id, $error_message);
            http_response_code(200);
            echo json_encode(['ok' => true]);
            exit;
        }

        try {
            $customer = Customer::find($customer_id);

            if (! $customer) {
                $error_message = "❌ عذراً، لم نتمكن من تفعيل إشعارات تيليجرام.\n\n";
                $error_message .= 'يرجى التأكد من استخدام رابط التفعيل الصحيح من حسابك.';

                $telegram_service->sendMessage($chat_id, $error_message);
                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            // Check if customer is already linked
            if ($customer->hasTelegramActivated()) {
                if ($customer->telegram_chat_id == $chat_id) {
                    // Same chat ID, already activated
                    $already_activated_message = "✅ <b>إشعارات تيليجرام مفعّلة بالفعل!</b>\n\n";
                    $already_activated_message .= "مرحباً {$customer->name},\n\n";
                    $already_activated_message .= "حسابك على تيليجرام مرتبط بالفعل بحساب المتجر.\n";
                    $already_activated_message .= 'ستستمر في تلقي إشعارات حول طلباتك والتحديثات المهمة.';

                    $telegram_service->sendMessage($chat_id, $already_activated_message);
                } else {
                    // Different chat ID, invalid action
                    $error_message = "❌ <b>إجراء غير صالح</b>\n\n";
                    $error_message .= "هذا الحساب مرتبط بالفعل بحساب تيليجرام آخر.\n\n";
                    $error_message .= 'إذا كنت تريد ربط حساب تيليجرام مختلف، يرجى الاتصال بالدعم.';

                    $telegram_service->sendMessage($chat_id, $error_message);
                }

                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            // Check if this chat_id is already linked to another customer
            $existing_customer = Customer::where('telegram_chat_id', $chat_id)->where('id', '!=', $customer_id)->first();
            if ($existing_customer) {
                $error_message = "❌ <b>إجراء غير صالح</b>\n\n";
                $error_message .= "هذا الحساب على تيليجرام مرتبط بالفعل بحساب عميل آخر.\n\n";
                $error_message .= 'يرجى استخدام حساب تيليجرام مختلف أو الاتصال بالدعم.';

                $telegram_service->sendMessage($chat_id, $error_message);
                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            $customer->telegram_chat_id = $chat_id;
            $customer->save();

            $confirmation_message = "✅ <b>تم تفعيل إشعارات تيليجرام!</b>\n\n";
            $confirmation_message .= "مرحباً {$customer->name},\n\n";
            $confirmation_message .= "تم ربط حسابك على تيليجرام بنجاح بحساب المتجر.\n";
            $confirmation_message .= 'ستتلقى الآن إشعارات حول طلباتك والتحديثات المهمة.';

            $telegram_service->sendMessage($chat_id, $confirmation_message);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Telegram webhook error (customer)', [
                'error' => $e->getMessage(),
                'customer_id' => $customer_id,
                'chat_id' => $chat_id,
            ]);

            $error_message = "❌ عذراً، حدث خطأ أثناء تفعيل إشعارات تيليجرام.\n\n";
            $error_message .= 'يرجى المحاولة مرة أخرى لاحقاً أو الاتصال بالدعم.';

            $telegram_service->sendMessage($chat_id, $error_message);
        }
    }
    // Handle store owner activation: user-123
    elseif (str_starts_with($start_param, 'user-')) {
        $user_id = (int) str_replace('user-', '', $start_param);

        if ($user_id <= 0) {
            $error_message = "❌ عذراً، لم نتمكن من تفعيل إشعارات تيليجرام.\n\n";
            $error_message .= 'يرجى التأكد من استخدام رابط التفعيل الصحيح من حسابك.';

            $telegram_service->sendMessage($chat_id, $error_message);
            http_response_code(200);
            echo json_encode(['ok' => true]);
            exit;
        }

        try {
            $user = User::find($user_id);

            if (! $user) {
                $error_message = "❌ عذراً، لم نتمكن من تفعيل إشعارات تيليجرام.\n\n";
                $error_message .= 'يرجى التأكد من استخدام رابط التفعيل الصحيح من حسابك.';

                $telegram_service->sendMessage($chat_id, $error_message);
                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            // Check if user is already linked
            if ($user->hasTelegramActivated()) {
                if ($user->telegram_chat_id == $chat_id) {
                    // Same chat ID, already activated
                    $already_activated_message = "✅ <b>إشعارات تيليجرام مفعّلة بالفعل!</b>\n\n";
                    $already_activated_message .= "مرحباً {$user->name},\n\n";
                    $already_activated_message .= "حسابك على تيليجرام مرتبط بالفعل بحساب مالك المتجر.\n";
                    $already_activated_message .= 'ستستمر في تلقي إشعارات حول الطلبات الجديدة وتنبيهات المخزون المنخفض والتحديثات المهمة.';

                    $telegram_service->sendMessage($chat_id, $already_activated_message);
                } else {
                    // Different chat ID, invalid action
                    $error_message = "❌ <b>إجراء غير صالح</b>\n\n";
                    $error_message .= "هذا الحساب مرتبط بالفعل بحساب تيليجرام آخر.\n\n";
                    $error_message .= 'إذا كنت تريد ربط حساب تيليجرام مختلف، يرجى الاتصال بالدعم.';

                    $telegram_service->sendMessage($chat_id, $error_message);
                }

                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            // Check if this chat_id is already linked to another user
            $existing_user = User::where('telegram_chat_id', $chat_id)->where('id', '!=', $user_id)->first();
            if ($existing_user) {
                $error_message = "❌ <b>إجراء غير صالح</b>\n\n";
                $error_message .= "هذا الحساب على تيليجرام مرتبط بالفعل بحساب مستخدم آخر.\n\n";
                $error_message .= 'يرجى استخدام حساب تيليجرام مختلف أو الاتصال بالدعم.';

                $telegram_service->sendMessage($chat_id, $error_message);
                http_response_code(200);
                echo json_encode(['ok' => true]);
                exit;
            }

            $user->telegram_chat_id = $chat_id;
            $user->save();

            $confirmation_message = "✅ <b>تم تفعيل إشعارات تيليجرام!</b>\n\n";
            $confirmation_message .= "مرحباً {$user->name},\n\n";
            $confirmation_message .= "تم ربط حسابك على تيليجرام بنجاح بحساب مالك المتجر.\n";
            $confirmation_message .= 'ستتلقى الآن إشعارات حول الطلبات الجديدة وتنبيهات المخزون المنخفض والتحديثات المهمة.';

            $telegram_service->sendMessage($chat_id, $confirmation_message);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Telegram webhook error (user)', [
                'error' => $e->getMessage(),
                'user_id' => $user_id,
                'chat_id' => $chat_id,
            ]);

            $error_message = "❌ عذراً، حدث خطأ أثناء تفعيل إشعارات تيليجرام.\n\n";
            $error_message .= 'يرجى المحاولة مرة أخرى لاحقاً أو الاتصال بالدعم.';

            $telegram_service->sendMessage($chat_id, $error_message);
        }
    } else {
        // Unknown parameter format
        $welcome_message = "👋 مرحباً!\n\n";
        $welcome_message .= 'لتفعيل إشعارات تيليجرام، يرجى استخدام رابط التفعيل من إعدادات حسابك.';

        $telegram_service->sendMessage($chat_id, $welcome_message);
    }
}

// Always return 200 OK to acknowledge receipt
http_response_code(200);
echo json_encode(['ok' => true]);
