<?php

namespace App\Filament\Resources\CityStoreDeliveries\Pages;

use App\Filament\Resources\CityStoreDeliveries\CityStoreDeliveryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCityStoreDelivery extends EditRecord
{
    protected static string $resource = CityStoreDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
