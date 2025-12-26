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
                Section::make(__('products.sections.basic_information'))
                    ->description(__('products.sections.basic_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('products.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('sku')
                            ->label(__('products.fields.sku'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->columnSpan(1),
                        Select::make('store_id')
                            ->label(__('products.fields.store_id'))
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
                            ->label(__('products.fields.type'))
                            ->options(ProductType::class)
                            ->default(ProductType::Physical)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->native(false)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label(__('products.fields.description'))
                            ->maxLength(2000)
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('products.sections.pricing_stock'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('products.fields.price'))
                            ->required()
                            ->numeric()
                            ->integer()
                            ->suffix('IQD')
                            ->minValue(0)
                            ->columnSpan(1),
                        TextInput::make('stock')
                            ->label(__('products.fields.stock'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),
                        Select::make('status')
                            ->label(__('products.fields.status'))
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Draft)
                            ->required(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->native(false)
                            ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make(__('products.sections.media'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('products.fields.image'))
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('products')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->rows(2)
                            ->helperText('Comma-separated keywords')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
