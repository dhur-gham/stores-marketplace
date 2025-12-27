<?php

namespace App\Filament\Resources\DiscountPlans\Schemas;

use App\Enums\DiscountType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('discount-plans.sections.plan_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('discount-plans.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('store_id')
                            ->label(__('discount-plans.fields.store'))
                            ->relationship('store', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Section::make(__('discount-plans.sections.discount_details'))
                    ->schema([
                        Select::make('discount_type')
                            ->label(__('discount-plans.fields.discount_type'))
                            ->options(DiscountType::class)
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->columnSpan(1),
                        TextInput::make('discount_value')
                            ->label(fn ($get) => $get('discount_type') === 'percentage'
                                ? __('discount-plans.fields.discount_value').' (%)'
                                : __('discount-plans.fields.discount_value').' (IQD)')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(fn ($get) => $get('discount_type') === 'percentage' ? 100 : null)
                            ->suffix(fn ($get) => $get('discount_type') === 'percentage' ? '%' : 'IQD')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Section::make(__('discount-plans.sections.schedule'))
                    ->schema([
                        DateTimePicker::make('start_date')
                            ->label(__('discount-plans.fields.start_date').' (Baghdad Time)')
                            ->required()
                            ->displayFormat('Y-m-d H:i')
                            ->native(false)
                            ->timezone('Asia/Baghdad')
                            ->columnSpan(1),
                        DateTimePicker::make('end_date')
                            ->label(__('discount-plans.fields.end_date').' (Baghdad Time)')
                            ->required()
                            ->after('start_date')
                            ->displayFormat('Y-m-d H:i')
                            ->native(false)
                            ->timezone('Asia/Baghdad')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}
