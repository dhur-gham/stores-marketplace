<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        $table->modifyQueryUsing(function ($query) {
            $query->with(['customer', 'store', 'city']);
        });

        return $table
            ->columns([
                Split::make([
                    TextColumn::make('id')
                        ->label(__('orders.messages.order_number'))
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('customer.name')
                            ->label(__('orders.fields.customer'))
                            ->searchable()
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('customer.email')
                            ->label(__('orders.messages.email'))
                            ->searchable()
                            ->color('gray'),
                    ]),
                    Stack::make([
                        TextColumn::make('store.name')
                            ->label(__('orders.fields.store'))
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('city.name')
                            ->label(__('orders.fields.city'))
                            ->searchable()
                            ->color('gray'),
                    ])->visibleFrom('lg'),
                    Stack::make([
                        TextColumn::make('total')
                            ->money('IQD')
                            ->sortable()
                            ->weight(FontWeight::Bold),
                        TextColumn::make('delivery_price')
                            ->money('IQD')
                            ->sortable()
                            ->color('gray')
                            ->prefix(__('orders.messages.delivery').': '),
                    ])->visibleFrom('md'),
                    TextColumn::make('status')
                        ->badge()
                        ->formatStateUsing(fn (OrderStatus $state): string => __('orders.status.'.$state->value))
                        ->color(fn (OrderStatus $state): string => match ($state) {
                            OrderStatus::New => 'info',
                            OrderStatus::Processing => 'primary',
                            OrderStatus::Dispatched => 'info',
                            OrderStatus::Complete => 'success',
                            OrderStatus::Cancelled => 'danger',
                        })
                        ->searchable(),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->label(__('orders.messages.date'))
                            ->dateTime()
                            ->sortable(),
                        TextColumn::make('updated_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->visibleFrom('xl'),
                ])->from('md'),

                // Mobile-only: show totals and status below main content
                Stack::make([
                    Split::make([
                        TextColumn::make('total')
                            ->money('IQD')
                            ->weight(FontWeight::Bold),
                        TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state): string => __('orders.status.'.$state->value))
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::New => 'info',
                                OrderStatus::Processing => 'primary',
                                OrderStatus::Dispatched => 'info',
                                OrderStatus::Complete => 'success',
                                OrderStatus::Cancelled => 'danger',
                            }),
                    ]),
                    Split::make([
                        TextColumn::make('store.name')
                            ->label(__('orders.fields.store'))
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label(__('orders.messages.date'))
                            ->date()
                            ->color('gray'),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.filters.status'))
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn ($status) => [$status->value => __('orders.status.'.$status->value)])
                            ->toArray()
                    )
                    ->native(false),
                SelectFilter::make('store_id')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('orders.filters.store'))
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
