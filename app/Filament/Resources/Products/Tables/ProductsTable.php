<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        // Filter products by user's stores if not super_admin
        if ($user && ! $user->hasRole('super_admin')) {
            $user_store_ids = $user->stores()->pluck('stores.id')->toArray();
            $table->modifyQueryUsing(function ($query) use ($user_store_ids) {
                $query->whereIn('store_id', $user_store_ids);
            });
        }

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
                    ->relationship('store', 'name', function ($query) {
                        // Filter stores in the filter dropdown
                        $user = auth()->user();
                        if ($user && ! $user->hasRole('super_admin')) {
                            $user_store_ids = $user->stores()->pluck('stores.id')->toArray();
                            $query->whereIn('stores.id', $user_store_ids);
                        }
                    })
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
                    BulkAction::make('publish')
                        ->label('Publish')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                        ->action(function (Collection $records): void {
                            $records->each(function (Product $product) {
                                $product->update(['status' => ProductStatus::Active]);
                            });

                            Notification::make()
                                ->title('Products published')
                                ->body($records->count().' product(s) have been published successfully.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('success'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
