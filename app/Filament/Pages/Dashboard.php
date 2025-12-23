<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    public function getHeading(): string
    {
        return 'Heloooo';
    }

    public function getSubheading(): ?string
    {
        if (! $this->canViewDashboardStats()) {
            return null;
        }

        $last_updated = Cache::get('dashboard_cache_updated_at');

        if ($last_updated) {
            return 'Last updated: '.Carbon::parse($last_updated)->diffForHumans();
        }

        return 'Data is being loaded for the first time';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_data')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn (): bool => $this->canViewDashboardStats())
                ->action(function () {
                    $this->clearDashboardCache();

                    Notification::make()
                        ->title('Dashboard Refreshed')
                        ->body('All dashboard data has been refreshed.')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }

    protected function canViewDashboardStats(): bool
    {
        return auth()->user()?->hasPermissionTo('view_dashboard_stats') ?? false;
    }

    protected function clearDashboardCache(): void
    {
        $cache_keys = [
            'widget_stats_overview',
            'widget_orders_chart',
            'widget_orders_by_status_chart',
            'widget_latest_orders',
            'dashboard_cache_updated_at',
        ];

        foreach ($cache_keys as $key) {
            Cache::forget($key);
        }

        Cache::put('dashboard_cache_updated_at', now(), 1800);
    }
}
