<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\StoreType;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Store;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Set default status for non-super_admin users if not set
        if (! $user?->hasRole('super_admin')) {
            // If status is not set or is Draft, default to Inactive
            if (! isset($data['status']) || $data['status'] === ProductStatus::Draft->value) {
                $data['status'] = ProductStatus::Inactive;
            }

            // Ensure store owners can only set Active or Inactive (not Draft)
            if (isset($data['status'])) {
                $status = $data['status'] instanceof ProductStatus
                    ? $data['status']
                    : ProductStatus::from($data['status']);

                if ($status === ProductStatus::Draft) {
                    $data['status'] = ProductStatus::Inactive;
                }
            }

            // Set store_id to user's first store if not set (since store field is hidden)
            if (! isset($data['store_id'])) {
                $user_store = $user->stores()->first();
                if ($user_store) {
                    $data['store_id'] = $user_store->id;
                }
            }
        }

        // Set type based on store type if not already set
        if (isset($data['store_id']) && ! isset($data['type'])) {
            $store = Store::find($data['store_id']);
            if ($store) {
                $data['type'] = $store->type === StoreType::Digital
                    ? ProductType::Digital
                    : ProductType::Physical;
            }
        }

        return $data;
    }
}
