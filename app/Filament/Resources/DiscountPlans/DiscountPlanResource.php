<?php

namespace App\Filament\Resources\DiscountPlans;

use App\Filament\Resources\DiscountPlans\Pages\CreateDiscountPlan;
use App\Filament\Resources\DiscountPlans\Pages\EditDiscountPlan;
use App\Filament\Resources\DiscountPlans\Pages\ListDiscountPlans;
use App\Filament\Resources\DiscountPlans\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\DiscountPlans\Schemas\DiscountPlanForm;
use App\Filament\Resources\DiscountPlans\Tables\DiscountPlansTable;
use App\Models\DiscountPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DiscountPlanResource extends Resource
{
    protected static ?string $model = DiscountPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Store Management';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('discount-plans.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discount-plans.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('discount-plans.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return DiscountPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountPlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountPlans::route('/'),
            'create' => CreateDiscountPlan::route('/create'),
            'edit' => EditDiscountPlan::route('/{record}/edit'),
        ];
    }
}
