<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->circular()
                        ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=P&background=random')
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold),
                        TextColumn::make('store.name')
                            ->label('Store')
                            ->searchable()
                            ->sortable()
                            ->color('gray'),
                    ]),
                    Stack::make([
                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (ProductStatus $state): string => match ($state) {
                                ProductStatus::Active => 'success',
                                ProductStatus::Inactive => 'danger',
                                ProductStatus::Draft => 'warning',
                            })
                            ->sortable(),
                        TextColumn::make('type')
                            ->badge()
                            ->color(fn (ProductType $state): string => match ($state) {
                                ProductType::Digital => 'info',
                                ProductType::Physical => 'success',
                            })
                            ->sortable(),
                    ])->visibleFrom('md'),
                    Stack::make([
                        TextColumn::make('price')
                            ->numeric()
                            ->suffix(' IQD')
                            ->sortable()
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('stock')
                            ->numeric()
                            ->sortable()
                            ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'gray')
                            ->prefix('Stock: '),
                    ])->visibleFrom('md'),
                    TextColumn::make('sku')
                        ->label('SKU')
                        ->searchable()
                        ->copyable()
                        ->color('gray')
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

                // Mobile-only: show badges and price/stock below main content
                Stack::make([
                    Split::make([
                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (ProductStatus $state): string => match ($state) {
                                ProductStatus::Active => 'success',
                                ProductStatus::Inactive => 'danger',
                                ProductStatus::Draft => 'warning',
                            }),
                        TextColumn::make('type')
                            ->badge()
                            ->color(fn (ProductType $state): string => match ($state) {
                                ProductType::Digital => 'info',
                                ProductType::Physical => 'success',
                            }),
                    ]),
                    Split::make([
                        TextColumn::make('price')
                            ->numeric()
                            ->suffix(' IQD')
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('stock')
                            ->numeric()
                            ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'gray')
                            ->prefix('Stock: '),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductStatus::class)
                    ->native(false),
                SelectFilter::make('type')
                    ->options(ProductType::class)
                    ->native(false),
                SelectFilter::make('store')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
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
