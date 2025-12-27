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
                Section::make(__('orders.sections.order_information'))
                    ->schema([
                        Select::make('status')
                            ->label(__('orders.fields.status'))
                            ->options(OrderStatus::class)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        TextInput::make('total')
                            ->label(__('orders.fields.total'))
                            ->numeric()
                            ->suffix('IQD')
                            ->disabled()
                            ->columnSpan(1),
                        TextInput::make('delivery_price')
                            ->label(__('orders.fields.delivery_price'))
                            ->numeric()
                            ->suffix('IQD')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make(__('orders.sections.notes_communication'))
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label(__('orders.form.internal_notes'))
                            ->helperText(__('orders.form.internal_notes_helper'))
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('customer_message')
                            ->label(__('orders.form.customer_message'))
                            ->helperText(__('orders.form.customer_message_helper'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
