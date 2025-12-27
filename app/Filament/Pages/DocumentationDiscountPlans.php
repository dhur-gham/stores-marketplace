<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DocumentationDiscountPlans extends Page
{
    protected string $view = 'filament.pages.documentation-resource';

    protected static bool $shouldRegisterNavigation = false;

    protected string $resource = 'discount_plans';

    public function getTitle(): string
    {
        return __('documentation.resources.discount_plans.title');
    }

    public function getHeading(): string
    {
        return __('documentation.resources.discount_plans.title');
    }

    public function getSubheading(): ?string
    {
        return __('documentation.resources.discount_plans.description');
    }

    public function getResourceData(): array
    {
        return __('documentation.resources.discount_plans');
    }
}
