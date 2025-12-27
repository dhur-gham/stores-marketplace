<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DocumentationCityStoreDeliveries extends Page
{
    protected string $view = 'filament.pages.documentation-resource';

    protected static bool $shouldRegisterNavigation = false;

    protected string $resource = 'city_store_deliveries';

    public function getTitle(): string
    {
        return __('documentation.resources.city_store_deliveries.title');
    }

    public function getHeading(): string
    {
        return __('documentation.resources.city_store_deliveries.title');
    }

    public function getSubheading(): ?string
    {
        return __('documentation.resources.city_store_deliveries.description');
    }

    public function getResourceData(): array
    {
        return __('documentation.resources.city_store_deliveries');
    }
}
