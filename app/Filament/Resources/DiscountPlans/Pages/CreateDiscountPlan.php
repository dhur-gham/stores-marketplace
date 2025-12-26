<?php

namespace App\Filament\Resources\DiscountPlans\Pages;

use App\Filament\Resources\DiscountPlans\DiscountPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountPlan extends CreateRecord
{
    protected static string $resource = DiscountPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // DateTimePicker with timezone('Asia/Baghdad') automatically converts to UTC for storage
        // No manual conversion needed

        // Set created_by_user_id
        $data['created_by_user_id'] = auth()->id();

        // Set store_id for non-super_admin users (first store they manage)
        if (! auth()->user()?->hasRole('super_admin')) {
            $user_stores = auth()->user()->stores()->pluck('stores.id');
            if ($user_stores->isEmpty()) {
                throw new \Exception('You must be assigned to at least one store to create a discount plan.');
            }
            $data['store_id'] = $user_stores->first();
        }

        return $data;
    }
}
