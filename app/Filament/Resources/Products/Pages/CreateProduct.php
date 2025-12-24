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

        // Set status to Draft for non-super_admin users
        if (! $user?->hasRole('super_admin')) {
            $data['status'] = ProductStatus::Draft;

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
