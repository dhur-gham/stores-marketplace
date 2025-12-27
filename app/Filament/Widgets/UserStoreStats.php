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

    /**
     * Clear cache for all users who have access to a specific store.
     */
    public static function clearCacheForStore(int $store_id): void
    {
        $store = Store::find($store_id);
        if (! $store) {
            return;
        }

        $user_ids = $store->users()->pluck('users.id')->toArray();

        foreach ($user_ids as $user_id) {
            Cache::forget('widget_user_store_stats_'.$user_id);
            Cache::forget('widget_user_store_stats_updated_at_'.$user_id);
            // Set new timestamp for immediate display
            Cache::put('widget_user_store_stats_updated_at_'.$user_id, now(), self::CACHE_TTL);
        }
    }

    /**
     * Clear cache for a specific user.
     */
    public static function clearCacheForUser(int $user_id): void
    {
        Cache::forget('widget_user_store_stats_'.$user_id);
        Cache::forget('widget_user_store_stats_updated_at_'.$user_id);
        // Set new timestamp for immediate display
        Cache::put('widget_user_store_stats_updated_at_'.$user_id, now(), self::CACHE_TTL);
    }

    /**
     * Get last update time for a specific user.
     */
    public static function getLastUpdateTime(int $user_id): ?\Illuminate\Support\Carbon
    {
        $cache_updated_key = 'widget_user_store_stats_updated_at_'.$user_id;
        $last_updated = Cache::get($cache_updated_key);

        if ($last_updated) {
            return \Illuminate\Support\Carbon::parse($last_updated);
        }

        return null;
    }

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
        $cache_updated_key = 'widget_user_store_stats_updated_at_'.$user->id;

        $stats = Cache::remember($cache_key, self::CACHE_TTL, function () use ($user, $cache_updated_key) {
            Cache::put($cache_updated_key, now(), self::CACHE_TTL);
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
                ->where('status', OrderStatus::Complete)
                ->sum('total');

            return [
                'total_stores' => count($store_ids),
                'total_orders' => count($order_ids),
                'total_items' => $total_items ?? 0,
                'total_sales' => $total_sales ?? 0,
            ];
        });

        return [
            Stat::make(__('dashboard.widgets.my_stores'), $stats['total_stores'])
                ->description(__('dashboard.widgets.my_stores_description'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make(__('dashboard.widgets.total_orders'), $stats['total_orders'])
                ->description(__('dashboard.widgets.total_orders_description'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make(__('dashboard.widgets.total_items_sold'), number_format($stats['total_items']))
                ->description(__('dashboard.widgets.total_items_sold_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make(__('dashboard.widgets.total_sales'), number_format($stats['total_sales']).' IQD')
                ->description(__('dashboard.widgets.total_sales_description'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
