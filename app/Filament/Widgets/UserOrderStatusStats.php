<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UserOrderStatusStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

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
            Cache::forget('widget_user_order_status_stats_'.$user_id);
        }
    }

    /**
     * Clear cache for a specific user.
     */
    public static function clearCacheForUser(int $user_id): void
    {
        Cache::forget('widget_user_order_status_stats_'.$user_id);
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

        $cache_key = 'widget_user_order_status_stats_'.$user->id;

        $stats = Cache::remember($cache_key, self::CACHE_TTL, function () use ($user) {
            // Get user's store IDs
            $store_ids = $user->stores()->pluck('stores.id')->toArray();

            if (empty($store_ids)) {
                return [
                    OrderStatus::New->value => 0,
                    OrderStatus::Processing->value => 0,
                    OrderStatus::Dispatched->value => 0,
                    OrderStatus::Complete->value => 0,
                    OrderStatus::Cancelled->value => 0,
                ];
            }

            // Count orders by status for user's stores
            $counts = [];
            foreach (OrderStatus::cases() as $status) {
                $counts[$status->value] = Order::query()
                    ->whereIn('store_id', $store_ids)
                    ->where('status', $status)
                    ->count();
            }

            return $counts;
        });

        $status_colors = [
            OrderStatus::New->value => 'primary',
            OrderStatus::Processing->value => 'warning',
            OrderStatus::Dispatched->value => 'info',
            OrderStatus::Complete->value => 'success',
            OrderStatus::Cancelled->value => 'danger',
        ];

        $status_icons = [
            OrderStatus::New->value => 'heroicon-m-sparkles',
            OrderStatus::Processing->value => 'heroicon-m-cog-6-tooth',
            OrderStatus::Dispatched->value => 'heroicon-m-truck',
            OrderStatus::Complete->value => 'heroicon-m-check-circle',
            OrderStatus::Cancelled->value => 'heroicon-m-x-circle',
        ];

        return [
            Stat::make(__('orders.status.new'), $stats[OrderStatus::New->value])
                ->description(__('dashboard.widgets.order_status.new_description'))
                ->descriptionIcon($status_icons[OrderStatus::New->value])
                ->color($status_colors[OrderStatus::New->value]),

            Stat::make(__('orders.status.processing'), $stats[OrderStatus::Processing->value])
                ->description(__('dashboard.widgets.order_status.processing_description'))
                ->descriptionIcon($status_icons[OrderStatus::Processing->value])
                ->color($status_colors[OrderStatus::Processing->value]),

            Stat::make(__('orders.status.dispatched'), $stats[OrderStatus::Dispatched->value])
                ->description(__('dashboard.widgets.order_status.dispatched_description'))
                ->descriptionIcon($status_icons[OrderStatus::Dispatched->value])
                ->color($status_colors[OrderStatus::Dispatched->value]),

            Stat::make(__('orders.status.complete'), $stats[OrderStatus::Complete->value])
                ->description(__('dashboard.widgets.order_status.complete_description'))
                ->descriptionIcon($status_icons[OrderStatus::Complete->value])
                ->color($status_colors[OrderStatus::Complete->value]),

            Stat::make(__('orders.status.cancelled'), $stats[OrderStatus::Cancelled->value])
                ->description(__('dashboard.widgets.order_status.cancelled_description'))
                ->descriptionIcon($status_icons[OrderStatus::Cancelled->value])
                ->color($status_colors[OrderStatus::Cancelled->value]),
        ];
    }
}
