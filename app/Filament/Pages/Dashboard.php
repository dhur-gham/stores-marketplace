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
        $user = auth()->user();

        if (! $user) {
            return __('dashboard.welcome');
        }

        // For super_admin users
        if ($this->canViewDashboardStats()) {
            return __('dashboard.welcome_admin', ['name' => $user->name]);
        }

        // For non-super_admin users (store owners)
        $stores = $user->stores()->pluck('name')->toArray();

        if (empty($stores)) {
            return __('dashboard.welcome', ['name' => $user->name]);
        }

        if (count($stores) === 1) {
            return __('dashboard.welcome_with_store', [
                'name' => $user->name,
                'store' => $stores[0],
            ]);
        }

        // Multiple stores
        $stores_list = implode(', ', array_slice($stores, 0, 2));
        if (count($stores) > 2) {
            $stores_list .= ' '.__('dashboard.and_more', ['count' => count($stores) - 2]);
        }

        return __('dashboard.welcome_with_stores', [
            'name' => $user->name,
            'stores' => $stores_list,
        ]);
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        // For super_admin users
        if ($this->canViewDashboardStats()) {
            $last_updated = Cache::get('dashboard_cache_updated_at');

            if ($last_updated) {
                return __('dashboard.last_updated', ['time' => Carbon::parse($last_updated)->diffForHumans()]);
            }

            return __('dashboard.data_loading_first_time');
        }

        // For non-super_admin users (store owners)
        if (! $user->hasRole('super_admin')) {
            $last_updated = \App\Filament\Widgets\UserStoreStats::getLastUpdateTime($user->id);

            if ($last_updated) {
                return __('dashboard.last_updated', ['time' => $last_updated->diffForHumans()]);
            }

            return __('dashboard.data_loading_first_time');
        }

        return null;
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        $actions = [];

        // For super_admin users
        if ($this->canViewDashboardStats()) {
            $actions[] = Action::make('refresh_data')
                ->label(__('dashboard.refresh_data'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->clearDashboardCache();

                    Notification::make()
                        ->title(__('dashboard.dashboard_refreshed'))
                        ->body(__('dashboard.dashboard_refreshed_body'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        // For non-super_admin users (store owners)
        if ($user && ! $user->hasRole('super_admin')) {
            $actions[] = Action::make('refresh_my_stats')
                ->label(__('dashboard.refresh_my_stats'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () use ($user) {
                    \App\Filament\Widgets\UserStoreStats::clearCacheForUser($user->id);

                    Notification::make()
                        ->title(__('dashboard.stats_refreshed'))
                        ->body(__('dashboard.stats_refreshed_body'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        return $actions;
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
