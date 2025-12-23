<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    private const CACHE_KEY = 'widget_stats_overview';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_dashboard_stats') ?? false;
    }

    protected function getStats(): array
    {
        $cached_data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            Cache::put('dashboard_cache_updated_at', now(), self::CACHE_TTL);

            return [
                'total_revenue' => Order::query()
                    ->where('status', OrderStatus::Completed)
                    ->sum('total'),
                'orders_this_month' => Order::query()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'revenue_this_month' => Order::query()
                    ->where('status', OrderStatus::Completed)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total'),
                'pending_orders' => Order::query()
                    ->whereIn('status', [OrderStatus::New, OrderStatus::Pending, OrderStatus::Processing])
                    ->count(),
                'total_products' => Product::count(),
                'total_stores' => Store::count(),
                'total_customers' => Customer::count(),
                'total_orders' => Order::count(),
            ];
        });

        return [
            Stat::make('Total Revenue', number_format($cached_data['total_revenue']).' IQD')
                ->description('All time completed orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Revenue This Month', number_format($cached_data['revenue_this_month']).' IQD')
                ->description('Completed orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Orders This Month', $cached_data['orders_this_month'])
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Pending Orders', $cached_data['pending_orders'])
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Products', $cached_data['total_products'])
                ->description('Active products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Total Stores', $cached_data['total_stores'])
                ->description('Registered stores')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Total Customers', $cached_data['total_customers'])
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Total Orders', $cached_data['total_orders'])
                ->description('All time')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
}
