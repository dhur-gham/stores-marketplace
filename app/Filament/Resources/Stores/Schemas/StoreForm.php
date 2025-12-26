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
                Section::make('Business Information')
                    ->schema([
                        Textarea::make('business_hours')
                            ->label('Business Hours')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Social Media')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('twitter_url')
                            ->label('Twitter URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->collapsible(),
                Section::make('Policies')
                    ->schema([
                        Textarea::make('return_policy')
                            ->label('Return Policy')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('shipping_policy')
                            ->label('Shipping Policy')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('privacy_policy')
                            ->label('Privacy Policy')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
