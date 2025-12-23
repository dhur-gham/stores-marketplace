<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiRequestsByEndpointChart extends ChartWidget
{
    protected ?string $heading = 'Top Endpoints (Last 7 Days)';

    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 1;

    private const CACHE_KEY = 'widget_api_requests_by_endpoint_chart';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', ApiRequest::class) ?? false;
    }

    protected function getData(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $endpoints = ApiRequest::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->select('path', DB::raw('count(*) as count'))
                ->groupBy('path')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $colors = [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1',
            ];

            return [
                'datasets' => [
                    [
                        'data' => $endpoints->pluck('count')->toArray(),
                        'backgroundColor' => array_slice($colors, 0, $endpoints->count()),
                    ],
                ],
                'labels' => $endpoints->pluck('path')->map(fn ($path) => strlen($path) > 25 ? '...'.substr($path, -22) : $path)->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
