<?php

namespace App\Filament\Resources\DiscountPlans\Tables;

use App\Enums\DiscountPlanStatus;
use App\Enums\DiscountType;
use App\Helpers\TimezoneHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DiscountPlansTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        // Filter discount plans by user's stores if not super_admin
        if ($user && ! $user->hasRole('super_admin')) {
            $user_store_ids = $user->stores()->pluck('stores.id')->toArray();
            $table->modifyQueryUsing(function ($query) use ($user_store_ids) {
                $query->whereIn('store_id', $user_store_ids);
            });
        }

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('discount-plans.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('store.name')
                    ->label(__('discount-plans.fields.store'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
                TextColumn::make('discount_type')
                    ->label(__('discount-plans.fields.discount_type'))
                    ->badge()
                    ->formatStateUsing(fn (DiscountType $state): string => __('discount-plans.discount_type.'.$state->value))
                    ->color(fn (DiscountType $state): string => $state === DiscountType::Percentage ? 'info' : 'success'),
                TextColumn::make('discount_value')
                    ->label(__('discount-plans.fields.discount_value'))
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->discount_type === DiscountType::Percentage) {
                            return "{$state}%";
                        }

                        return "{$state} IQD";
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('discount-plans.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (DiscountPlanStatus $state): string => __('discount-plans.status.'.$state->value))
                    ->color(fn (DiscountPlanStatus $state): string => match ($state) {
                        DiscountPlanStatus::Scheduled => 'warning',
                        DiscountPlanStatus::Active => 'success',
                        DiscountPlanStatus::Expired => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('discount-plans.fields.start_date'))
                    ->dateTime('Y-m-d H:i')
                    ->formatStateUsing(fn ($state) => $state ? TimezoneHelper::formatBaghdad($state, 'Y-m-d H:i') : '-')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('discount-plans.fields.end_date'))
                    ->dateTime('Y-m-d H:i')
                    ->formatStateUsing(fn ($state) => $state ? TimezoneHelper::formatBaghdad($state, 'Y-m-d H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('discount-plans.filters.status'))
                    ->options(
                        collect(DiscountPlanStatus::cases())
                            ->mapWithKeys(fn ($status) => [$status->value => __('discount-plans.status.'.$status->value)])
                            ->toArray()
                    )
                    ->native(false),
                SelectFilter::make('discount_type')
                    ->label(__('discount-plans.filters.discount_type'))
                    ->options(
                        collect(DiscountType::cases())
                            ->mapWithKeys(fn ($type) => [$type->value => __('discount-plans.discount_type.'.$type->value)])
                            ->toArray()
                    )
                    ->native(false),
                SelectFilter::make('store_id')
                    ->label(__('discount-plans.filters.store'))
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
