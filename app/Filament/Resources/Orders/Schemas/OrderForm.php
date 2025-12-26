<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(OrderStatus::class)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->suffix('IQD')
                            ->disabled()
                            ->columnSpan(1),
                        TextInput::make('delivery_price')
                            ->label('Delivery Price')
                            ->numeric()
                            ->suffix('IQD')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('Notes & Communication')
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->helperText('Private notes visible only to store owners')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('customer_message')
                            ->label('Customer Message')
                            ->helperText('Message to send to customer via Telegram')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
