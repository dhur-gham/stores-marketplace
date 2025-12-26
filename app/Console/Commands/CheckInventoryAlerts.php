<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckInventoryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock products and send alerts to store owners';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram_service): int
    {
        $this->info('Checking inventory alerts...');

        $stores = Store::where('status', 'active')->get();
        $alert_count = 0;

        foreach ($stores as $store) {
            $low_stock_products = $store->getLowStockProducts();

            if ($low_stock_products->isEmpty()) {
                continue;
            }

            // Get store owners
            $store_owners = $store->users()->whereNotNull('telegram_chat_id')->get();

            if ($store_owners->isEmpty()) {
                continue;
            }

            // Build alert message
            $message = "⚠️ <b>Low Stock Alert</b>\n\n";
            $message .= "Store: <b>{$store->name}</b>\n\n";
            $message .= "The following products are running low on stock:\n\n";

            foreach ($low_stock_products as $product) {
                $message .= "• <b>{$product->name}</b> - Stock: {$product->stock}\n";
            }

            // Send to all store owners
            foreach ($store_owners as $owner) {
                try {
                    $telegram_service->sendMessage($owner->telegram_chat_id, $message);
                    $alert_count++;
                } catch (\Exception $e) {
                    Log::error("Failed to send inventory alert to store owner {$owner->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Sent {$alert_count} inventory alerts.");

        return Command::SUCCESS;
    }
}
