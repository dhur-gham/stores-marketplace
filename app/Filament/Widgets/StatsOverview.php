<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_dashboard_stats') ?? false;
    }

    protected function getStats(): array
    {
        $total_revenue = Order::query()
            ->where('status', OrderStatus::Completed)
            ->sum('total');

        $orders_this_month = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $revenue_this_month = Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $pending_orders = Order::query()
            ->whereIn('status', [OrderStatus::New, OrderStatus::Pending, OrderStatus::Processing])
            ->count();

        return [
            Stat::make('Total Revenue', number_format($total_revenue).' IQD')
                ->description('All time completed orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Revenue This Month', number_format($revenue_this_month).' IQD')
                ->description('Completed orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Orders This Month', $orders_this_month)
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Pending Orders', $pending_orders)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Products', Product::count())
                ->description('Active products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Total Stores', Store::count())
                ->description('Registered stores')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Total Orders', Order::count())
                ->description('All time')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
}
