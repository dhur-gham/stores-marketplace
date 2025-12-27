<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StoreOwnerNotificationService
{
    public function __construct(public TelegramService $telegram_service) {}

    /**
     * Send notification to store owners when a new order is placed.
     */
    public function notifyNewOrder(Order $order): void
    {
        $store = $order->store;
        $store_owners = $store->users()->whereNotNull('telegram_chat_id')->get();

        if ($store_owners->isEmpty()) {
            return;
        }

        $message = "🛒 <b>New Order Received</b>\n\n";
        $message .= "Order #{$order->id}\n";
        $message .= "Store: <b>{$store->name}</b>\n";
        $message .= "Customer: {$order->customer->name}\n";
        $message .= "Total: {$order->total} IQD\n";
        $message .= "Status: {$order->status->value}\n\n";
        $message .= 'View order details in your dashboard.';

        foreach ($store_owners as $owner) {
            try {
                $this->telegram_service->sendMessageToStoreOwner($owner, $message);
            } catch (\Exception $e) {
                Log::error("Failed to send new order notification to store owner {$owner->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send notification to store owners when order status changes.
     */
    public function notifyOrderStatusChange(Order $order, OrderStatus $old_status, OrderStatus $new_status): void
    {
        $store = $order->store;
        $store_owners = $store->users()->whereNotNull('telegram_chat_id')->get();

        if ($store_owners->isEmpty()) {
            return;
        }

        $message = "📦 <b>Order Status Updated</b>\n\n";
        $message .= "Order #{$order->id}\n";
        $message .= "Store: <b>{$store->name}</b>\n";
        $message .= "Status changed from: {$old_status->value}\n";
        $message .= "To: <b>{$new_status->value}</b>\n\n";
        $message .= 'View order details in your dashboard.';

        foreach ($store_owners as $owner) {
            try {
                $this->telegram_service->sendMessageToStoreOwner($owner, $message);
            } catch (\Exception $e) {
                Log::error("Failed to send order status change notification to store owner {$owner->id}: {$e->getMessage()}");
            }
        }
    }
}
