<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(public TelegramService $telegram_service) {}

    /**
     * Handle incoming Telegram webhook updates.
     */
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        // Log the update for debugging (remove in production if needed)
        Log::info('Telegram webhook received', ['update' => $update]);

        // Handle message updates
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        // Always return 200 OK to acknowledge receipt
        return response()->json(['ok' => true]);
    }

    /**
     * Handle incoming message.
     */
    private function handleMessage(array $message): void
    {
        // Check if it's a /start command
        if (! isset($message['text']) || ! str_starts_with($message['text'], '/start')) {
            return;
        }

        $text = $message['text'];
        $chat_id = $message['chat']['id'] ?? null;
        $from = $message['from'] ?? [];

        if (! $chat_id) {
            Log::warning('Telegram message missing chat_id', ['message' => $message]);

            return;
        }

        // Extract customer ID from /start command
        // Format: /start cust-123
        $parts = explode(' ', $text, 2);
        $start_parameter = $parts[1] ?? null;

        if (! $start_parameter || ! str_starts_with($start_parameter, 'cust-')) {
            // No customer ID provided, send generic welcome message
            $this->sendWelcomeMessage($chat_id);

            return;
        }

        // Extract customer ID
        $customer_id = (int) str_replace('cust-', '', $start_parameter);

        if ($customer_id <= 0) {
            Log::warning('Invalid customer ID in Telegram start parameter', [
                'parameter' => $start_parameter,
                'chat_id' => $chat_id,
            ]);
            $this->sendErrorMessage($chat_id);

            return;
        }

        // Find customer
        $customer = Customer::find($customer_id);

        if (! $customer) {
            Log::warning('Customer not found for Telegram activation', [
                'customer_id' => $customer_id,
                'chat_id' => $chat_id,
            ]);
            $this->sendErrorMessage($chat_id);

            return;
        }

        // Update customer with chat_id
        $customer->telegram_chat_id = $chat_id;
        $customer->save();

        // Send confirmation message
        $this->sendActivationConfirmation($chat_id, $customer);
    }

    /**
     * Send welcome message when no customer ID is provided.
     */
    private function sendWelcomeMessage(int $chat_id): void
    {
        $message = "👋 Welcome!\n\n";
        $message .= 'To activate Telegram notifications, please use the activation link from your account settings.';

        $this->telegram_service->sendMessage($chat_id, $message);
    }

    /**
     * Send error message when activation fails.
     */
    private function sendErrorMessage(int $chat_id): void
    {
        $message = "❌ Sorry, we couldn't activate your Telegram notifications.\n\n";
        $message .= "Please make sure you're using the correct activation link from your account.";

        $this->telegram_service->sendMessage($chat_id, $message);
    }

    /**
     * Send activation confirmation message.
     */
    private function sendActivationConfirmation(int $chat_id, Customer $customer): void
    {
        $message = "✅ <b>Telegram Notifications Activated!</b>\n\n";
        $message .= "Hello {$customer->name},\n\n";
        $message .= "Your Telegram account has been successfully linked to your store account.\n";
        $message .= 'You will now receive notifications about your orders and important updates.';

        $this->telegram_service->sendMessage($chat_id, $message);
    }
}
