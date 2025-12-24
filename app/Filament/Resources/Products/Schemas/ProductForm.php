<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\StoreType;
use App\Models\Store;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Product details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->columnSpan(1),
                        Select::make('store_id')
                            ->relationship('store', 'name')
                            ->required(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->reactive()
                            ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $store = Store::find($state);
                                    if ($store) {
                                        // Map StoreType to ProductType
                                        $product_type = $store->type === StoreType::Digital
                                            ? ProductType::Digital
                                            : ProductType::Physical;
                                        $set('type', $product_type);
                                    }
                                }
                            })
                            ->columnSpan(1),
                        Select::make('type')
                            ->options(ProductType::class)
                            ->default(ProductType::Physical)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->native(false)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->maxLength(2000)
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Pricing & Stock')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->suffix('IQD')
                            ->minValue(0)
                            ->columnSpan(1),
                        TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),
                        Select::make('status')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Draft)
                            ->required(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->native(false)
                            ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('Media')
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('products')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
