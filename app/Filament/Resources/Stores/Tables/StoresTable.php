<?php

namespace App\Filament\Resources\Stores\Tables;

use App\Enums\StoreStatus;
use App\Enums\StoreType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->circular()
                        ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=S&background=random')
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold),
                        TextColumn::make('slug')
                            ->searchable()
                            ->copyable()
                            ->color('gray')
                            ->toggleable(isToggledHiddenByDefault: true),
                    ]),
                    Stack::make([
                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (StoreStatus $state): string => match ($state) {
                                StoreStatus::Active => 'success',
                                StoreStatus::Inactive => 'danger',
                            })
                            ->sortable(),
                        TextColumn::make('type')
                            ->badge()
                            ->color(fn (StoreType $state): string => match ($state) {
                                StoreType::Digital => 'info',
                                StoreType::Physical => 'success',
                            })
                            ->sortable(),
                        TextColumn::make('products_count')
                            ->label('Products')
                            ->counts('products')
                            ->badge()
                            ->color('gray')
                            ->sortable(),
                    ])->visibleFrom('md'),
                    TextColumn::make('bio')
                        ->limit(50)
                        ->searchable()
                        ->toggleable()
                        ->visibleFrom('lg'),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                        TextColumn::make('updated_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->visibleFrom('xl'),
                ])->from('md'),

                // Mobile-only: show status, type and products below main content
                Stack::make([
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn (StoreStatus $state): string => match ($state) {
                            StoreStatus::Active => 'success',
                            StoreStatus::Inactive => 'danger',
                        }),
                    TextColumn::make('type')
                        ->badge()
                        ->color(fn (StoreType $state): string => match ($state) {
                            StoreType::Digital => 'info',
                            StoreType::Physical => 'success',
                        }),
                    TextColumn::make('products_count')
                        ->label('Products')
                        ->counts('products')
                        ->badge()
                        ->color('gray'),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(StoreStatus::class)
                    ->native(false),
                SelectFilter::make('type')
                    ->options(StoreType::class)
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
