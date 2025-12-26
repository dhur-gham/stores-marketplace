<?php

namespace App\Filament\Resources\DiscountPlans\RelationManagers;

use App\Livewire\ProductSelect;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->size(50),
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->numeric()
                    ->suffix(' IQD')
                    ->sortable(),
                TextColumn::make('discounted_price')
                    ->label('Discounted Price')
                    ->numeric()
                    ->suffix(' IQD')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('associate_product')
                    ->label('Add Products')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Hidden::make('product_ids')
                            ->default('[]')
                            ->dehydrated(),
                        View::make('livewire.product-select-wrapper')
                            ->viewData([
                                'plan_id' => $this->ownerRecord->id,
                                'store_id' => $this->ownerRecord->store_id,
                            ]),
                    ])
                    ->action(function (array $data) {
                        $product_ids = json_decode($data['product_ids'] ?? '[]', true);
                        if (! empty($product_ids) && is_array($product_ids)) {
                            $this->ownerRecord->products()->attach($product_ids);
                            // Trigger discount recalculation after attaching products
                            app(\App\Services\DiscountService::class)->activatePlan($this->ownerRecord);
                        }
                    })
                    ->successNotificationTitle('Products added successfully'),
            ])
            ->recordActions([
                Action::make('detach')
                    ->label('Remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Product $record) {
                        $this->ownerRecord->products()->detach($record->id);
                    })
                    ->successNotificationTitle('Product removed successfully'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('detach')
                        ->label('Remove selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $product_ids = $records->pluck('id')->toArray();
                            $this->ownerRecord->products()->detach($product_ids);
                        })
                        ->successNotificationTitle('Products removed successfully'),
                ]),
            ]);
    }
}
