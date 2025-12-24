<?php

namespace App\Filament\Resources\CityStoreDeliveries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityStoreDeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Delivery Price')
                    ->description('Set delivery price for a city')
                    ->schema([
                        Select::make('store_id')
                            ->relationship('store', 'name', function ($query) {
                                $user = auth()->user();
                                if ($user && ! $user->hasRole('super_admin')) {
                                    $user_store_ids = $user->stores()->pluck('stores.id')->toArray();
                                    $query->whereIn('stores.id', $user_store_ids);
                                }

                                return $query;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->disabled()
                            ->columnSpan(1),
                        Select::make('city_id')
                            ->relationship('city', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->disabled()
                            ->columnSpan(1),
                        TextInput::make('price')
                            ->label('Delivery Price')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->suffix('IQD')
                            ->minValue(0)
                            ->default(0)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
