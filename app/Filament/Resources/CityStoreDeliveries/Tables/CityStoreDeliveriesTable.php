<?php

namespace App\Filament\Resources\CityStoreDeliveries\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CityStoreDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('store.name')
                            ->label('Store')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold),
                        TextColumn::make('city.name')
                            ->label('City')
                            ->searchable()
                            ->sortable()
                            ->color('gray'),
                    ]),
                    TextColumn::make('price')
                        ->label('Delivery Price')
                        ->numeric()
                        ->suffix(' IQD')
                        ->sortable()
                        ->color(fn ($state) => $state == 0 ? 'success' : 'warning')
                        ->weight(FontWeight::SemiBold),
                    TextColumn::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime()
                        ->sortable()
                        ->toggleable()
                        ->visibleFrom('md'),
                ])->from('md'),

                // Mobile-only: show price below store/city
                TextColumn::make('price')
                    ->label('Delivery Price')
                    ->numeric()
                    ->suffix(' IQD')
                    ->color(fn ($state) => $state == 0 ? 'success' : 'warning')
                    ->weight(FontWeight::SemiBold)
                    ->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('store')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('edit_price')
                        ->label('Update Price')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->form([
                            TextInput::make('price')
                                ->label('Delivery Price')
                                ->numeric()
                                ->integer()
                                ->required()
                                ->minValue(0)
                                ->suffix('IQD'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['price' => $data['price']]);
                            });

                            Notification::make()
                                ->title('Prices updated')
                                ->body($records->count().' records updated successfully.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('store.name')
            ->groups([
                'store.name',
            ]);
    }
}
