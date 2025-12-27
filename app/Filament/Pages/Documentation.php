<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Documentation extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected string $view = 'filament.pages.documentation';

    protected static string|UnitEnum|null $navigationGroup = 'Help & Support';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('documentation.navigation_label');
    }

    public function getTitle(): string
    {
        return __('documentation.title');
    }

    public function getHeading(): string
    {
        return __('documentation.heading');
    }

    public function getSubheading(): ?string
    {
        return __('documentation.subheading');
    }
}
