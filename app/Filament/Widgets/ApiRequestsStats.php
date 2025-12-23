<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ApiRequestsStats extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    private const CACHE_KEY = 'widget_api_requests_stats';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', ApiRequest::class) ?? false;
    }

    protected function getStats(): array
    {
        $cached_data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $total_requests = ApiRequest::count();
            $requests_today = ApiRequest::whereDate('created_at', today())->count();
            $avg_response_time = ApiRequest::avg('duration_ms') ?? 0;
            $avg_response_time_today = ApiRequest::whereDate('created_at', today())->avg('duration_ms') ?? 0;
            $error_count = ApiRequest::where('status_code', '>=', 400)->count();
            $error_rate = $total_requests > 0 ? ($error_count / $total_requests) * 100 : 0;
            $slow_requests = ApiRequest::where('duration_ms', '>', 500)->count();

            return [
                'total_requests' => $total_requests,
                'requests_today' => $requests_today,
                'avg_response_time' => round($avg_response_time, 2),
                'avg_response_time_today' => round($avg_response_time_today, 2),
                'error_rate' => round($error_rate, 2),
                'error_count' => $error_count,
                'slow_requests' => $slow_requests,
            ];
        });

        return [
            Stat::make('Total Requests', number_format($cached_data['total_requests']))
                ->description('All time')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Requests Today', number_format($cached_data['requests_today']))
                ->description('Since midnight')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Avg Response Time', $cached_data['avg_response_time'].' ms')
                ->description('All time average')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($cached_data['avg_response_time'] < 200 ? 'success' : ($cached_data['avg_response_time'] < 500 ? 'warning' : 'danger')),

            Stat::make('Avg Response Today', $cached_data['avg_response_time_today'].' ms')
                ->description('Today\'s average')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($cached_data['avg_response_time_today'] < 200 ? 'success' : ($cached_data['avg_response_time_today'] < 500 ? 'warning' : 'danger')),

            Stat::make('Error Rate', $cached_data['error_rate'].'%')
                ->description($cached_data['error_count'].' total errors')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($cached_data['error_rate'] < 1 ? 'success' : ($cached_data['error_rate'] < 5 ? 'warning' : 'danger')),

            Stat::make('Slow Requests', number_format($cached_data['slow_requests']))
                ->description('Over 500ms')
                ->descriptionIcon('heroicon-m-clock')
                ->color($cached_data['slow_requests'] < 10 ? 'success' : ($cached_data['slow_requests'] < 50 ? 'warning' : 'danger')),
        ];
    }
}
