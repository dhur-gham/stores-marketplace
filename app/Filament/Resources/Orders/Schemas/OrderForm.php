<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('store_id')
                            ->relationship('store', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('delivery_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->default('new')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
