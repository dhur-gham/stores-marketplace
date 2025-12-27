<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DocumentationProducts extends Page
{
    protected string $view = 'filament.pages.documentation-resource';

    protected static bool $shouldRegisterNavigation = false;

    protected string $resource = 'products';

    public function getTitle(): string
    {
        return __('documentation.resources.products.title');
    }

    public function getHeading(): string
    {
        return __('documentation.resources.products.title');
    }

    public function getSubheading(): ?string
    {
        return __('documentation.resources.products.description');
    }

    public function getResourceData(): array
    {
        return __('documentation.resources.products');
    }
}
