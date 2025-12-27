<?php

namespace App\Filament\Resources\DiscountPlans\RelationManagers;

use App\Models\Product;
use App\Services\DiscountService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('discount-plans.relation_managers.products.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label(__('discount-plans.relation_managers.products.columns.image'))
                    ->disk('public')
                    ->circular()
                    ->size(50),
                TextColumn::make('name')
                    ->label(__('discount-plans.relation_managers.products.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('discount-plans.relation_managers.products.columns.sku'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('price')
                    ->label(__('discount-plans.relation_managers.products.columns.price'))
                    ->numeric()
                    ->suffix(' IQD')
                    ->sortable(),
                TextColumn::make('discounted_price')
                    ->label(__('discount-plans.relation_managers.products.columns.discounted_price'))
                    ->numeric()
                    ->suffix(' IQD')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('stock')
                    ->label(__('discount-plans.relation_managers.products.columns.stock'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('associate_product')
                    ->label(__('discount-plans.relation_managers.products.actions.add_products'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('product_ids')
                            ->label(__('discount-plans.relation_managers.products.form.products'))
                            ->multiple()
                            ->options(function () {
                                $plan_id = $this->ownerRecord->id;
                                $store_id = $this->ownerRecord->store_id;

                                // Get product IDs already in this plan
                                $existing_product_ids = DB::table('discount_plan_products')
                                    ->where('plan_id', $plan_id)
                                    ->pluck('product_id')
                                    ->toArray();

                                return Product::query()
                                    ->where('store_id', $store_id)
                                    ->when(! empty($existing_product_ids), function ($q) use ($existing_product_ids) {
                                        return $q->whereNotIn('id', $existing_product_ids);
                                    })
                                    ->limit(50)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $plan_id = $this->ownerRecord->id;
                                $store_id = $this->ownerRecord->store_id;

                                // Get product IDs already in this plan
                                $existing_product_ids = DB::table('discount_plan_products')
                                    ->where('plan_id', $plan_id)
                                    ->pluck('product_id')
                                    ->toArray();

                                return Product::query()
                                    ->where('store_id', $store_id)
                                    ->when(! empty($existing_product_ids), function ($q) use ($existing_product_ids) {
                                        return $q->whereNotIn('id', $existing_product_ids);
                                    })
                                    ->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $product_ids = $data['product_ids'] ?? [];
                        if (! empty($product_ids)) {
                            DB::transaction(function () use ($product_ids) {
                                // Attach products to the plan
                                $this->ownerRecord->products()->attach($product_ids);

                                // Apply discount if plan is active
                                $discount_service = app(DiscountService::class);
                                $discount_service->applyDiscountToProducts($this->ownerRecord, $product_ids);
                            });
                        }
                    })
                    ->successNotificationTitle(__('discount-plans.relation_managers.products.notifications.products_added')),
            ])
            ->recordActions([
                Action::make('detach')
                    ->label(__('discount-plans.relation_managers.products.actions.remove'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Product $record) {
                        DB::transaction(function () use ($record) {
                            $plan_id = $this->ownerRecord->id;

                            // Detach from the many-to-many relationship
                            $this->ownerRecord->products()->detach($record->id);

                            // If this product's active plan_id matches this plan, clear the discount
                            if ($record->plan_id === $plan_id) {
                                $record->plan_id = null;
                                $record->discounted_price = null;
                                $record->save();
                            }
                        });
                    })
                    ->successNotificationTitle(__('discount-plans.relation_managers.products.notifications.product_removed')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('detach')
                        ->label(__('discount-plans.relation_managers.products.actions.remove_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                $plan_id = $this->ownerRecord->id;
                                $product_ids = $records->pluck('id')->toArray();

                                // Detach from the many-to-many relationship
                                $this->ownerRecord->products()->detach($product_ids);

                                // Clear discount for products that have this plan as their active plan
                                Product::whereIn('id', $product_ids)
                                    ->where('plan_id', $plan_id)
                                    ->update([
                                        'plan_id' => null,
                                        'discounted_price' => null,
                                    ]);
                            });
                        })
                        ->successNotificationTitle(__('discount-plans.relation_managers.products.notifications.products_removed')),
                ]),
            ]);
    }
}
