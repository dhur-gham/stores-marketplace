<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 4;

    private const CACHE_KEY = 'widget_latest_orders';

    private const CACHE_TTL = 1800; // 30 minutes

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_latest_orders') ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest Orders';

    public function table(Table $table): Table
    {
        $order_ids = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Order::query()
                ->latest()
                ->limit(10)
                ->pluck('id')
                ->toArray();
        });

        return $table
            ->query(
                Order::query()
                    ->whereIn('id', $order_ids)
                    ->with(['customer', 'store', 'city'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->store?->name ?? '')
                    ->visibleFrom('sm'),

                TextColumn::make('store.name')
                    ->label('Store')
                    ->searchable()
                    ->visibleFrom('lg'),

                TextColumn::make('city.name')
                    ->label('City')
                    ->searchable()
                    ->visibleFrom('xl'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IQD')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::New => 'info',
                        OrderStatus::Pending => 'warning',
                        OrderStatus::Processing => 'primary',
                        OrderStatus::Completed => 'success',
                        OrderStatus::Cancelled => 'danger',
                        OrderStatus::Refunded => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
