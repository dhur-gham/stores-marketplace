<?php

namespace App\Filament\Resources\CityStoreDeliveries\Pages;

use App\Filament\Resources\CityStoreDeliveries\CityStoreDeliveryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCityStoreDeliveries extends ListRecords
{
    protected static string $resource = CityStoreDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('initialize')
                ->label('Initialize Store')
                ->icon('heroicon-o-plus-circle')
                ->url(CityStoreDeliveryResource::getUrl('initialize')),
        ];
    }
}
