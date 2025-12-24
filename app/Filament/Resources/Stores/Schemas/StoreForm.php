<?php

namespace App\Filament\Resources\Stores\Schemas;

use App\Enums\StoreStatus;
use App\Enums\StoreType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store Information')
                    ->description('Basic store details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('type')
                            ->options(StoreType::class)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        Select::make('status')
                            ->options(StoreStatus::class)
                            ->default(StoreStatus::Active)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        Textarea::make('bio')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Media')
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('stores')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
