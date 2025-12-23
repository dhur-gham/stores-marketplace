<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ApiResponseTimeChart extends ChartWidget
{
    protected ?string $heading = 'API Response Time (Last 7 Days)';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    private const CACHE_KEY = 'widget_api_response_time_chart';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', ApiRequest::class) ?? false;
    }

    protected function getData(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $days = collect(range(6, 0))->map(fn ($days_ago) => Carbon::today()->subDays($days_ago));

            $labels = $days->map(fn ($date) => $date->format('M j'));

            $avg_times = $days->map(function ($date) {
                return round(
                    ApiRequest::whereDate('created_at', $date)->avg('duration_ms') ?? 0,
                    2
                );
            });

            $max_times = $days->map(function ($date) {
                return round(
                    ApiRequest::whereDate('created_at', $date)->max('duration_ms') ?? 0,
                    2
                );
            });

            $request_counts = $days->map(function ($date) {
                return ApiRequest::whereDate('created_at', $date)->count();
            });

            return [
                'datasets' => [
                    [
                        'label' => 'Avg Response Time (ms)',
                        'data' => $avg_times->values()->toArray(),
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                    ],
                    [
                        'label' => 'Max Response Time (ms)',
                        'data' => $max_times->values()->toArray(),
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => false,
                    ],
                ],
                'labels' => $labels->values()->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
