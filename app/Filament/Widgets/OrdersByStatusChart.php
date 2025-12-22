<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Orders by Status';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_orders_by_status_chart') ?? false;
    }

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = [];
        $counts = [];
        $colors = [];

        $status_colors = [
            OrderStatus::New->value => 'rgba(59, 130, 246, 0.8)',
            OrderStatus::Pending->value => 'rgba(245, 158, 11, 0.8)',
            OrderStatus::Processing->value => 'rgba(139, 92, 246, 0.8)',
            OrderStatus::Completed->value => 'rgba(34, 197, 94, 0.8)',
            OrderStatus::Cancelled->value => 'rgba(239, 68, 68, 0.8)',
            OrderStatus::Refunded->value => 'rgba(107, 114, 128, 0.8)',
        ];

        foreach (OrderStatus::cases() as $status) {
            $count = Order::where('status', $status)->count();
            if ($count > 0) {
                $statuses[] = ucfirst($status->value);
                $counts[] = $count;
                $colors[] = $status_colors[$status->value];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $statuses,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
