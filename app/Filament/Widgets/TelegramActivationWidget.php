<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TelegramActivationWidget extends Widget
{
    protected string $view = 'filament.widgets.telegram-activation';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ! $user->hasRole('super_admin') && ! $user->hasTelegramActivated();
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'activation_link' => $user?->getTelegramDeepLink() ?? null,
            'is_activated' => $user?->hasTelegramActivated() ?? false,
        ];
    }
}
