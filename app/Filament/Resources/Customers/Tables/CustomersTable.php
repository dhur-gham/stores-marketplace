<?php

namespace App\Filament\Resources\Customers\Tables;

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

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('id')
                        ->label('#')
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('email')
                            ->searchable()
                            ->sortable()
                            ->copyable()
                            ->color('gray'),
                    ]),
                    TextColumn::make('phone')
                        ->searchable()
                        ->icon('heroicon-m-phone')
                        ->visibleFrom('md'),
                    TextColumn::make('city.name')
                        ->label('City')
                        ->sortable()
                        ->icon('heroicon-m-map-pin')
                        ->visibleFrom('lg'),
                    TextColumn::make('orders_count')
                        ->label('Orders')
                        ->counts('orders')
                        ->badge()
                        ->color('primary')
                        ->sortable(),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->label('Joined')
                            ->dateTime()
                            ->sortable(),
                        TextColumn::make('updated_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->visibleFrom('xl'),
                ])->from('md'),

                // Mobile-only: show phone, city, and orders below main content
                Stack::make([
                    Split::make([
                        TextColumn::make('phone')
                            ->icon('heroicon-m-phone')
                            ->color('gray'),
                        TextColumn::make('orders_count')
                            ->label('Orders')
                            ->counts('orders')
                            ->badge()
                            ->color('primary'),
                    ]),
                    Split::make([
                        TextColumn::make('city.name')
                            ->label('City')
                            ->icon('heroicon-m-map-pin')
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label('Joined')
                            ->date()
                            ->color('gray'),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->label('City')
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
