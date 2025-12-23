<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Orders Overview';

    protected static ?int $sort = 2;

    private const CACHE_KEY = 'widget_orders_chart';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_orders_chart') ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->getOrdersPerMonth();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data['orders_count'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Revenue (thousands)',
                    'data' => $data['revenue'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function getOrdersPerMonth(): array
    {
        $months = [];
        $orders_count = [];
        $revenue = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthly_orders = Order::query()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);

            $orders_count[] = (clone $monthly_orders)->count();
            $revenue[] = round((clone $monthly_orders)->sum('total') / 1000, 1);
        }

        return [
            'months' => $months,
            'orders_count' => $orders_count,
            'revenue' => $revenue,
        ];
    }
}
