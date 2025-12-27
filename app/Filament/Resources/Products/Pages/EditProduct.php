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

        // Auto-generate SEO fields if they're empty
        if (empty($data['meta_title']) && ! empty($data['name'])) {
            $store_name = $data['store_id'] ? Store::find($data['store_id'])?->name : '';
            $meta_title = $store_name ? "{$data['name']} - {$store_name}" : $data['name'];
            $data['meta_title'] = \Illuminate\Support\Str::limit($meta_title, 60);
        }

        if (empty($data['meta_description'])) {
            if (! empty($data['description'])) {
                $data['meta_description'] = \Illuminate\Support\Str::limit(strip_tags($data['description']), 160);
            } elseif (! empty($data['name'])) {
                $data['meta_description'] = \Illuminate\Support\Str::limit("Buy {$data['name']} online. High quality product with best prices.", 160);
            }
        }

        if (empty($data['meta_keywords']) && ! empty($data['name'])) {
            $keywords = [];
            $name_words = explode(' ', \Illuminate\Support\Str::lower($data['name']));
            $keywords = array_merge($keywords, array_filter($name_words, fn ($word) => strlen($word) > 3));

            if (! empty($data['description'])) {
                $description_words = explode(' ', \Illuminate\Support\Str::lower(strip_tags($data['description'])));
                $keywords = array_merge($keywords, array_filter($description_words, fn ($word) => strlen($word) > 3));
            }

            if (! empty($data['store_id'])) {
                $store = Store::find($data['store_id']);
                if ($store?->name) {
                    $store_words = explode(' ', \Illuminate\Support\Str::lower($store->name));
                    $keywords = array_merge($keywords, array_filter($store_words, fn ($word) => strlen($word) > 3));
                }
            }

            $keywords = array_unique($keywords);
            $keywords = array_slice($keywords, 0, 10);
            $data['meta_keywords'] = implode(', ', $keywords);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        // Store owners can only set status to Active or Inactive (not Draft)
        if (! $user?->hasRole('super_admin') && isset($data['status'])) {
            $status = $data['status'] instanceof ProductStatus
                ? $data['status']
                : ProductStatus::from($data['status']);

            // If store owner tries to set Draft, prevent it and keep current status
            if ($status === ProductStatus::Draft) {
            unset($data['status']);
            }
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
