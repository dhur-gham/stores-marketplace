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
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Auto-generate SEO fields when name changes (only if they're empty)
                                if (! empty($state)) {
                                    // Auto-generate meta_title if empty
                                    if (empty($get('meta_title'))) {
                                        $store_name = $get('store_id') ? Store::find($get('store_id'))?->name : '';
                                        $meta_title = $store_name ? "{$state} - {$store_name}" : $state;
                                        $set('meta_title', \Illuminate\Support\Str::limit($meta_title, 60));
                                    }

                                    // Auto-generate meta_description if empty
                                    if (empty($get('meta_description'))) {
                                        if (! empty($get('description'))) {
                                            $set('meta_description', \Illuminate\Support\Str::limit(strip_tags($get('description')), 160));
                                        } else {
                                            $set('meta_description', \Illuminate\Support\Str::limit("Buy {$state} online. High quality product with best prices.", 160));
                                        }
                                    }

                                    // Auto-generate meta_keywords if empty
                                    if (empty($get('meta_keywords'))) {
                                        $keywords = [];
                                        $name_words = explode(' ', \Illuminate\Support\Str::lower($state));
                                        $keywords = array_merge($keywords, array_filter($name_words, fn ($word) => strlen($word) > 3));

                                        if (! empty($get('description'))) {
                                            $description_words = explode(' ', \Illuminate\Support\Str::lower(strip_tags($get('description'))));
                                            $keywords = array_merge($keywords, array_filter($description_words, fn ($word) => strlen($word) > 3));
                                        }

                                        if ($get('store_id')) {
                                            $store = Store::find($get('store_id'));
                                            if ($store?->name) {
                                                $store_words = explode(' ', \Illuminate\Support\Str::lower($store->name));
                                                $keywords = array_merge($keywords, array_filter($store_words, fn ($word) => strlen($word) > 3));
                                            }
                                        }

                                        $keywords = array_unique($keywords);
                                        $keywords = array_slice($keywords, 0, 10);
                                        $set('meta_keywords', implode(', ', $keywords));
                                    }
                                }
                            })
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
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Auto-generate meta_description when description changes (only if empty)
                                if (empty($get('meta_description'))) {
                                    if (! empty($state)) {
                                        $set('meta_description', \Illuminate\Support\Str::limit(strip_tags($state), 160));
                                    } elseif (! empty($get('name'))) {
                                        $set('meta_description', \Illuminate\Support\Str::limit("Buy {$get('name')} online. High quality product with best prices.", 160));
                                    }
                                }

                                // Auto-generate meta_keywords when description changes (only if empty)
                                if (empty($get('meta_keywords')) && ! empty($get('name'))) {
                                    $keywords = [];
                                    $name_words = explode(' ', \Illuminate\Support\Str::lower($get('name')));
                                    $keywords = array_merge($keywords, array_filter($name_words, fn ($word) => strlen($word) > 3));

                                    if (! empty($state)) {
                                        $description_words = explode(' ', \Illuminate\Support\Str::lower(strip_tags($state)));
                                        $keywords = array_merge($keywords, array_filter($description_words, fn ($word) => strlen($word) > 3));
                                    }

                                    if ($get('store_id')) {
                                        $store = Store::find($get('store_id'));
                                        if ($store?->name) {
                                            $store_words = explode(' ', \Illuminate\Support\Str::lower($store->name));
                                            $keywords = array_merge($keywords, array_filter($store_words, fn ($word) => strlen($word) > 3));
                                        }
                                    }

                                    $keywords = array_unique($keywords);
                                    $keywords = array_slice($keywords, 0, 10);
                                    $set('meta_keywords', implode(', ', $keywords));
                                }
                            })
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
                            ->helperText('Auto-generated from product name. Can be customized.')
                            ->columnSpanFull(),
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Auto-generated from product name. Recommended: 50-60 characters.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Auto-generate if empty when name changes
                                if (empty($state) && ! empty($get('name'))) {
                                    $store_name = $get('store_id') ? Store::find($get('store_id'))?->name : '';
                                    $meta_title = $store_name ? "{$get('name')} - {$store_name}" : $get('name');
                                    $set('meta_title', \Illuminate\Support\Str::limit($meta_title, 60));
                                }
                            })
                            ->columnSpanFull(),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Auto-generated from description. Recommended: 150-160 characters.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Auto-generate if empty when description changes
                                if (empty($state)) {
                                    if (! empty($get('description'))) {
                                        $set('meta_description', \Illuminate\Support\Str::limit(strip_tags($get('description')), 160));
                                    } elseif (! empty($get('name'))) {
                                        $set('meta_description', \Illuminate\Support\Str::limit("Buy {$get('name')} online. High quality product with best prices.", 160));
                                    }
                                }
                            })
                            ->columnSpanFull(),
                        Textarea::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->rows(2)
                            ->helperText('Auto-generated from product name and description. Comma-separated keywords.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
            ]);
    }
}
