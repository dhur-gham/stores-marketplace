<?php

namespace App\Filament\Resources\DiscountPlans\Pages;

use App\Filament\Resources\DiscountPlans\DiscountPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountPlan extends EditRecord
{
    protected static string $resource = DiscountPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // DateTimePicker with timezone('Asia/Baghdad') handles conversion automatically
        // Data is stored in UTC, Filament converts to Baghdad for display
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // DateTimePicker with timezone('Asia/Baghdad') automatically converts back to UTC for storage
        // No manual conversion needed
        return $data;
    }
}
