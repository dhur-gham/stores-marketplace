<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductType;
use App\Enums\StoreType;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Store;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure type is set based on store type when loading the form
        if (isset($data['store_id'])) {
            $store = Store::find($data['store_id']);
            if ($store) {
                $data['type'] = $store->type === StoreType::Digital
                    ? ProductType::Digital
                    : ProductType::Physical;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent non-super_admin users from changing status
        if (! auth()->user()?->hasRole('super_admin')) {
            // Keep the existing status, don't allow changes
            unset($data['status']);
        }

        // Update type based on store type if store_id changed
        if (isset($data['store_id'])) {
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
