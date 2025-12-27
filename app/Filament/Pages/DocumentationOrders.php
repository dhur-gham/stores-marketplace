<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DocumentationOrders extends Page
{
    protected string $view = 'filament.pages.documentation-resource';

    protected static bool $shouldRegisterNavigation = false;

    protected string $resource = 'orders';

    public function getTitle(): string
    {
        return __('documentation.resources.orders.title');
    }

    public function getHeading(): string
    {
        return __('documentation.resources.orders.title');
    }

    public function getSubheading(): ?string
    {
        return __('documentation.resources.orders.description');
    }

    public function getResourceData(): array
    {
        return __('documentation.resources.orders');
    }
}
