<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $bot_token;

    private string $api_url;

    public function __construct()
    {
        $this->bot_token = config('services.telegram.bot_token', env('BOT_TOKEN'));
        $this->api_url = "https://api.telegram.org/bot{$this->bot_token}";
    }

    /**
     * Send a message to a Telegram chat.
     *
     * @param  int  $chat_id  The Telegram chat ID
     * @param  string  $message  The message text
     * @param  string|null  $parse_mode  Parse mode: 'HTML', 'Markdown', or 'MarkdownV2'
     * @return array<string, mixed>|null
     */
    public function sendMessage(int $chat_id, string $message, ?string $parse_mode = 'HTML'): ?array
    {
        if (empty($this->bot_token)) {
            Log::error('Telegram bot token is not configured');

            return null;
        }

        try {
            $response = Http::post("{$this->api_url}/sendMessage", [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => $parse_mode,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['ok']) && $result['ok'] === true) {
                return $result;
            }

            // Log error if request failed
            if (isset($result['description'])) {
                Log::warning('Telegram API error', [
                    'chat_id' => $chat_id,
                    'error' => $result['description'],
                    'error_code' => $result['error_code'] ?? null,
                ]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'chat_id' => $chat_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Send a message to a customer.
     *
     * @param  \App\Models\Customer  $customer  The customer to send message to
     * @param  string  $message  The message text
     * @param  string|null  $parse_mode  Parse mode
     * @return array<string, mixed>|null
     */
    public function sendMessageToCustomer(\App\Models\Customer $customer, string $message, ?string $parse_mode = 'HTML'): ?array
    {
        if (! $customer->hasTelegramActivated()) {
            Log::warning('Attempted to send Telegram message to customer without chat_id', [
                'customer_id' => $customer->id,
            ]);

            return null;
        }

        return $this->sendMessage($customer->telegram_chat_id, $message, $parse_mode);
    }

    /**
     * Format a notification message with HTML.
     *
     * @param  string  $title  Message title
     * @param  string  $content  Message content
     * @param  array<string, string>  $additional_data  Additional key-value pairs
     */
    public function formatMessage(string $title, string $content, array $additional_data = []): string
    {
        $message = "<b>{$title}</b>\n\n{$content}";

        if (! empty($additional_data)) {
            $message .= "\n\n";
            foreach ($additional_data as $key => $value) {
                $message .= "<b>{$key}:</b> {$value}\n";
            }
        }

        return $message;
    }
}
