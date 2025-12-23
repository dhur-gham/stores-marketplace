<?php

namespace App\Filament\Resources\ApiRequests\Pages;

use App\Filament\Resources\ApiRequests\ApiRequestResource;
use App\Filament\Widgets\ApiErrorsTable;
use App\Filament\Widgets\ApiRequestsByEndpointChart;
use App\Filament\Widgets\ApiRequestsStats;
use App\Filament\Widgets\ApiResponseTimeChart;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListApiRequests extends ListRecords
{
    protected static string $resource = ApiRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_metrics')
                ->label('Refresh Metrics')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->clearApiMetricsCache();

                    Notification::make()
                        ->title('Metrics Refreshed')
                        ->body('API metrics data has been refreshed.')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ApiRequestsStats::class,
            ApiResponseTimeChart::class,
            ApiRequestsByEndpointChart::class,
            ApiErrorsTable::class,
        ];
    }

    protected function clearApiMetricsCache(): void
    {
        $cache_keys = [
            'widget_api_requests_stats',
            'widget_api_response_time_chart',
            'widget_api_requests_by_endpoint_chart',
        ];

        foreach ($cache_keys as $key) {
            Cache::forget($key);
        }
    }
}
