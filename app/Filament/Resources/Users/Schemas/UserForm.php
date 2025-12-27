<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->description('Basic user account details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255)
                            ->confirmed()
                            ->revealable(),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->revealable(),
                    ])
                    ->columns(2),
                Section::make('Roles & Permissions')
                    ->description('Assign roles to this user')
                    ->schema([
                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),
                        Select::make('stores')
                            ->multiple()
                            ->relationship('stores', 'name')
                            ->preload()
                            ->searchable()
                            ->label('Managed Stores'),
                    ])
                    ->columns(2),
                Section::make('Telegram Notifications')
                    ->description('Link Telegram account to receive order notifications')
                    ->schema([
                        Placeholder::make('telegram_status')
                            ->label(__('dashboard.telegram.status'))
                            ->content(function ($record) {
                                if (! $record) {
                                    return __('dashboard.telegram.not_activated');
                                }

                                return $record->hasTelegramActivated()
                                    ? __('dashboard.telegram.activated')
                                    : __('dashboard.telegram.not_activated');
                            }),
                        Placeholder::make('telegram_chat_id')
                            ->label(__('dashboard.telegram.chat_id'))
                            ->content(function ($record) {
                                return $record?->telegram_chat_id ?? __('dashboard.telegram.no_chat_id');
                            })
                            ->visible(fn ($record) => $record && $record->hasTelegramActivated()),
                        ViewField::make('telegram_activation_link')
                            ->label(__('dashboard.telegram.activation_link'))
                            ->view('filament.forms.components.telegram-activation-link')
                            ->visible(fn ($record) => $record && ! $record->hasTelegramActivated()),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
