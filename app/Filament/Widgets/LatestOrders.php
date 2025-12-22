<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('view_latest_orders') ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'store', 'city'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('store.name')
                    ->label('Store')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label('City'),

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
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
