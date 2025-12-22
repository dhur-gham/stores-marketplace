<?php

namespace App\Filament\Resources\CityStoreDeliveries;

use App\Filament\Resources\CityStoreDeliveries\Pages\EditCityStoreDelivery;
use App\Filament\Resources\CityStoreDeliveries\Pages\InitializeStoreDelivery;
use App\Filament\Resources\CityStoreDeliveries\Pages\ListCityStoreDeliveries;
use App\Filament\Resources\CityStoreDeliveries\Schemas\CityStoreDeliveryForm;
use App\Filament\Resources\CityStoreDeliveries\Tables\CityStoreDeliveriesTable;
use App\Models\CityStoreDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CityStoreDeliveryResource extends Resource
{
    protected static ?string $model = CityStoreDelivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Delivery Prices';

    protected static ?string $modelLabel = 'Delivery Price';

    protected static ?string $pluralModelLabel = 'Delivery Prices';

    protected static string|UnitEnum|null $navigationGroup = 'Store Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CityStoreDeliveryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CityStoreDeliveriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCityStoreDeliveries::route('/'),
            'initialize' => InitializeStoreDelivery::route('/initialize'),
            'edit' => EditCityStoreDelivery::route('/{record}/edit'),
        ];
    }
}
