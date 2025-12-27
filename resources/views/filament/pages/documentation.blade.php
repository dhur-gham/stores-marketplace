<x-filament-panels::page>
    <div class="space-y-6">
        <div class="prose dark:prose-invert max-w-none">
            <p class="text-lg text-gray-600 dark:text-gray-400">
                {{ __('documentation.intro') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $resource_pages = [
                    'products' => \App\Filament\Pages\DocumentationProducts::class,
                    'discount_plans' => \App\Filament\Pages\DocumentationDiscountPlans::class,
                    'orders' => \App\Filament\Pages\DocumentationOrders::class,
                    'city_store_deliveries' => \App\Filament\Pages\DocumentationCityStoreDeliveries::class,
                ];
            @endphp
            @foreach(__('documentation.resources') as $key => $resource)
                @if(isset($resource_pages[$key]))
                    <a href="{{ $resource_pages[$key]::getUrl() }}" 
                       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center">
                                    @if(isset($resource['icon']))
                                        <x-filament::icon icon="{{ $resource['icon'] }}" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ $resource['title'] }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $resource['description'] }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

