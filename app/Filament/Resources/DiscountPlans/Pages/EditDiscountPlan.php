<?php

namespace App\Filament\Resources\DiscountPlans\Pages;

use App\Filament\Resources\DiscountPlans\DiscountPlanResource;
use App\Services\DiscountService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountPlan extends EditRecord
{
    protected static string $resource = DiscountPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    // Remove discounts from products before deleting the plan
                    $discount_service = app(DiscountService::class);
                    $discount_service->removePlanDiscounts($this->record);
                }),
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

    protected function afterSave(): void
    {
        // Recalculate discounts for all products in the plan if discount value/type changed
        $discount_service = app(DiscountService::class);
        $discount_service->updatePlanProducts($this->record);
    }
}
