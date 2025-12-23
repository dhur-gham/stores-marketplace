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
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('id')
                        ->label('Order #')
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('customer.name')
                            ->label('Customer')
                            ->searchable()
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('customer.email')
                            ->label('Email')
                            ->searchable()
                            ->color('gray'),
                    ]),
                    Stack::make([
                        TextColumn::make('store.name')
                            ->label('Store')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('city.name')
                            ->label('City')
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
                            ->prefix('Delivery: '),
                    ])->visibleFrom('md'),
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn (OrderStatus $state): string => match ($state) {
                            OrderStatus::New => 'info',
                            OrderStatus::Pending => 'warning',
                            OrderStatus::Processing => 'primary',
                            OrderStatus::Completed => 'success',
                            OrderStatus::Cancelled => 'danger',
                            OrderStatus::Refunded => 'gray',
                        })
                        ->searchable(),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->label('Date')
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
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::New => 'info',
                                OrderStatus::Pending => 'warning',
                                OrderStatus::Processing => 'primary',
                                OrderStatus::Completed => 'success',
                                OrderStatus::Cancelled => 'danger',
                                OrderStatus::Refunded => 'gray',
                            }),
                    ]),
                    Split::make([
                        TextColumn::make('store.name')
                            ->label('Store')
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label('Date')
                            ->date()
                            ->color('gray'),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->native(false),
                SelectFilter::make('store_id')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Store')
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
