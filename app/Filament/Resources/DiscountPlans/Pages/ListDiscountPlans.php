<?php

namespace App\Filament\Resources\DiscountPlans\Pages;

use App\Filament\Resources\DiscountPlans\DiscountPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountPlans extends ListRecords
{
    protected static string $resource = DiscountPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
