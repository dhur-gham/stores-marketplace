<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CustomerMessageService
{
    public function __construct(public TelegramService $telegram_service) {}

    /**
     * Send a message to a customer via Telegram.
     *
     * @param  Customer  $customer  The customer to send message to
     * @param  string  $message  The message text
     * @return bool Success status
     */
    public function sendMessage(Customer $customer, string $message): bool
    {
        if (! $customer->hasTelegramActivated()) {
            Log::warning('Attempted to send Telegram message to customer without chat_id', [
                'customer_id' => $customer->id,
            ]);

            return false;
        }

        $result = $this->telegram_service->sendMessageToCustomer($customer, $message);

        return $result !== null;
    }

    /**
     * Send an order-related message to a customer.
     *
     * @param  Order  $order  The order
     * @param  string  $message  The message text
     * @return bool Success status
     */
    public function sendOrderMessage(Order $order, string $message): bool
    {
        $formatted_message = "📦 <b>Order #{$order->id} Update</b>\n\n";
        $formatted_message .= $message;

        return $this->sendMessage($order->customer, $formatted_message);
    }
}

