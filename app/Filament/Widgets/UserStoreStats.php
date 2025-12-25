<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UserStoreStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ! $user->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $cache_key = 'widget_user_store_stats_'.$user->id;

        $stats = Cache::remember($cache_key, self::CACHE_TTL, function () use ($user) {
            // Get user's store IDs
            $store_ids = $user->stores()->pluck('stores.id')->toArray();

            if (empty($store_ids)) {
                return [
                    'total_stores' => 0,
                    'total_orders' => 0,
                    'total_items' => 0,
                    'total_sales' => 0,
                ];
            }

            // Get orders for user's stores
            $order_ids = Order::query()
                ->whereIn('store_id', $store_ids)
                ->pluck('id')
                ->toArray();

            // Calculate total items (sum of quantities from order_items)
            $total_items = OrderItem::query()
                ->whereIn('order_id', $order_ids)
                ->sum('quantity');

            // Calculate total sales from completed orders
            $total_sales = Order::query()
                ->whereIn('store_id', $store_ids)
                ->where('status', OrderStatus::Completed)
                ->sum('total');

            return [
                'total_stores' => count($store_ids),
                'total_orders' => count($order_ids),
                'total_items' => $total_items ?? 0,
                'total_sales' => $total_sales ?? 0,
            ];
        });

        return [
            Stat::make('My Stores', $stats['total_stores'])
                ->description('Stores you manage')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Total Orders', $stats['total_orders'])
                ->description('All orders from your stores')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Total Items Sold', number_format($stats['total_items']))
                ->description('Items sold across all orders')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Total Sales', number_format($stats['total_sales']).' IQD')
                ->description('Revenue from completed orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
