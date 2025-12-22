<x-filament-panels::page>
    <form wire:submit="initialize">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Initialize Delivery Prices
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
