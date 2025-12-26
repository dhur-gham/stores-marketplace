<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(public TelegramService $telegram_service) {}

    /**
     * Place order from cart, grouping items by store (one order per store).
     *
     * @param  array<string, array<string, mixed>>|null  $address_data  Array keyed by store_id with city_id and address
     * @return array<int, Order>
     */
    public function place_order(Customer $customer, ?array $address_data = null): array
    {
        $cart_items = $customer->cart_items()->with('product.store')->get();

        if ($cart_items->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        // Validate all products are active and have sufficient stock
        foreach ($cart_items as $cart_item) {
            if ($cart_item->product->status !== ProductStatus::Active) {
                throw new \InvalidArgumentException("Product {$cart_item->product->name} is not available");
            }

            if ($cart_item->product->stock < $cart_item->quantity) {
                throw new \InvalidArgumentException("Insufficient stock for product {$cart_item->product->name}");
            }
        }

        // Group cart items by store
        $items_by_store = $cart_items->groupBy('product.store_id');

        $orders = [];

        DB::beginTransaction();

        try {
            foreach ($items_by_store as $store_id => $store_cart_items) {
                $store = $store_cart_items->first()->product->store;

                // Validate address requirements
                if ($store->isPhysical()) {
                    $store_address_data = $address_data[$store_id] ?? null;

                    if (! $store_address_data || ! isset($store_address_data['city_id']) || ! isset($store_address_data['address'])) {
                        throw new \InvalidArgumentException("Address and city are required for physical store: {$store->name}");
                    }

                    if (empty($store_address_data['address'])) {
                        throw new \InvalidArgumentException("Address cannot be empty for physical store: {$store->name}");
                    }

                    $city_id = (int) $store_address_data['city_id'];
                    $address = $store_address_data['address'];
                    $delivery_price = $this->calculate_delivery_price($store, $city_id);
                } else {
                    // Digital store - no address needed
                    $city_id = null;
                    $address = null;
                    $delivery_price = 0;
                }

                // Calculate items total
                $items_total = $store_cart_items->sum(fn (CartItem $item) => $item->quantity * $item->price);
                $total = $items_total + $delivery_price;

                // Create order
                $order = Order::query()->create([
                    'customer_id' => $customer->id,
                    'store_id' => $store_id,
                    'city_id' => $city_id,
                    'address' => $address,
                    'total' => $total,
                    'delivery_price' => $delivery_price,
                    'status' => OrderStatus::New,
                ]);

                // Record initial status in history (system change, no user)
                $order->recordStatusChange(OrderStatus::New);

                // Create order items and update stock
                foreach ($store_cart_items as $cart_item) {
                    // Use cart item price (already includes discount if available when added to cart)
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $cart_item->product_id,
                        'quantity' => $cart_item->quantity,
                        'price' => $cart_item->price,
                    ]);

                    // Update product stock
                    $cart_item->product->decrement('stock', $cart_item->quantity);
                }

                // Clear cart items for this store
                CartItem::query()
                    ->where('customer_id', $customer->id)
                    ->whereIn('id', $store_cart_items->pluck('id'))
                    ->delete();

                $orders[] = $order;
            }

            DB::commit();

            // Send Telegram notifications for all orders
            $store_owner_notification_service = app(\App\Services\StoreOwnerNotificationService::class);

            foreach ($orders as $order) {
                // Notify customer if Telegram activated
                if ($customer->hasTelegramActivated()) {
                    $this->sendOrderNotification($customer, $order);
                }

                // Notify store owners
                $store_owner_notification_service->notifyNewOrder($order);
            }

            return $orders;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate delivery price from city-store relationship.
     */
    public function calculate_delivery_price(Store $store, ?int $city_id): int
    {
        if ($store->isDigital() || ! $city_id) {
            return 0;
        }

        $delivery = DB::table('city_store_delivery')
            ->where('store_id', $store->id)
            ->where('city_id', $city_id)
            ->first();

        if (! $delivery) {
            // If city not in store's delivery cities, return 0 or throw?
            // For now, return 0 (can be changed based on business logic)
            return 0;
        }

        // Price is stored as unsignedBigInteger in pivot (already in whole units)
        return (int) $delivery->price;
    }

    /**
     * Get paginated orders for customer.
     *
     * @return array{data: array<int, array<string, mixed>>, paginator: LengthAwarePaginator}
     */
    public function get_customer_orders(Customer $customer, int $per_page = 15, int $page = 1): array
    {
        $paginator = $customer->orders()
            ->with('store', 'city')
            ->latest()
            ->paginate($per_page, ['*'], 'page', $page);

        $data = $paginator->items();

        $orders_data = array_map(function (Order $order) {
            return [
                'id' => $order->id,
                'store' => [
                    'id' => $order->store->id,
                    'name' => $order->store->name,
                    'slug' => $order->store->slug,
                    'image' => $order->store->image ? asset('storage/'.$order->store->image) : null,
                ],
                'city' => $order->city ? [
                    'id' => $order->city->id,
                    'name' => $order->city->name,
                ] : null,
                'address' => $order->address,
                'total' => $order->total,
                'delivery_price' => $order->delivery_price,
                'status' => $order->status->value,
                'created_at' => $order->created_at->toISOString(),
            ];
        }, $data);

        return [
            'data' => $orders_data,
            'paginator' => $paginator,
        ];
    }

    /**
     * Get single order with full details.
     *
     * @return array<string, mixed>|null
     */
    public function get_order(Customer $customer, int $order_id): ?array
    {
        $order = Order::query()
            ->where('id', $order_id)
            ->where('customer_id', $customer->id)
            ->with('store', 'city', 'order_items.product.store')
            ->first();

        if (! $order) {
            return null;
        }

        $order_items = $order->order_items->map(function (OrderItem $item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'image' => $item->product->image ? asset('storage/'.$item->product->image) : null,
                    'store' => [
                        'id' => $item->product->store->id,
                        'name' => $item->product->store->name,
                    ],
                ],
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ];
        })->toArray();

        $items_total = $order->order_items->sum(fn (OrderItem $item) => $item->quantity * $item->price);

        return [
            'id' => $order->id,
            'store' => [
                'id' => $order->store->id,
                'name' => $order->store->name,
                'slug' => $order->store->slug,
                'image' => $order->store->image ? asset('storage/'.$order->store->image) : null,
                'type' => $order->store->type->value,
            ],
            'city' => $order->city ? [
                'id' => $order->city->id,
                'name' => $order->city->name,
            ] : null,
            'address' => $order->address,
            'items' => $order_items,
            'items_total' => $items_total,
            'delivery_price' => $order->delivery_price,
            'total' => $order->total,
            'status' => $order->status->value,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
        ];
    }

    /**
     * Send Telegram notification for a new order.
     */
    private function sendOrderNotification(Customer $customer, Order $order): void
    {
        // Load order relationships
        $order->load(['store', 'city', 'order_items.product']);

        $title = 'تم إنشاء طلبك بنجاح';
        $content = "تم إنشاء طلبك رقم #{$order->id} من متجر {$order->store->name}";

        $items_list = '';
        $items_total = 0;

        foreach ($order->order_items as $item) {
            $item_subtotal = $item->quantity * $item->price;
            $items_total += $item_subtotal;
            $items_list .= "• {$item->product->name} × {$item->quantity} = {$item_subtotal} IQD\n";
        }

        // Build message manually for better formatting
        $message = "<b>{$title}</b>\n\n{$content}\n\n";
        $message .= "<b>المتجر:</b> {$order->store->name}\n";

        if ($order->city) {
            $message .= "<b>المدينة:</b> {$order->city->name}\n";
        }

        if ($order->address) {
            $message .= "<b>العنوان:</b> {$order->address}\n";
        }

        $message .= "\n<b>المنتجات:</b>\n{$items_list}\n";
        $message .= "<b>إجمالي المنتجات:</b> {$items_total} IQD\n";

        if ($order->delivery_price > 0) {
            $message .= "<b>رسوم التوصيل:</b> {$order->delivery_price} IQD\n";
        }

        $message .= "<b>الإجمالي:</b> <b>{$order->total} IQD</b>\n";
        $message .= "<b>الحالة:</b> {$order->status->value}";

        $this->telegram_service->sendMessageToCustomer($customer, $message);
    }
}
