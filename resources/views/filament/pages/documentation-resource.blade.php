<x-filament-panels::page>
    @php
        $data = $this->getResourceData();
    @endphp

    <div class="space-y-6">
        <div class="prose dark:prose-invert max-w-none">
            <div class="mb-6">
                <a href="{{ \App\Filament\Pages\Documentation::getUrl() }}" 
                   class="inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                    <x-filament::icon icon="heroicon-o-arrow-left" class="w-4 h-4" />
                    {{ __('documentation.back_to_documentation') }}
                </a>
            </div>

            @if(isset($data['overview']))
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('documentation.overview') }}
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $data['overview'] }}
                    </p>
                </div>
            @endif

            @if(isset($data['benefits']) && is_array($data['benefits']))
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('documentation.benefits') }}
                    </h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                        @foreach($data['benefits'] as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($data['requirements']) && is_array($data['requirements']))
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('documentation.requirements') }}
                    </h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                        @foreach($data['requirements'] as $requirement)
                            <li>{{ $requirement }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($data['how_to_use']) && is_array($data['how_to_use']))
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('documentation.how_to_use') }}
                    </h2>
                    <div class="space-y-4">
                        @foreach($data['how_to_use'] as $index => $step)
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-400 font-semibold">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-gray-700 dark:text-gray-300">{{ $step }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($data['tips']) && is_array($data['tips']))
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('documentation.tips') }}
                    </h2>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                            @foreach($data['tips'] as $tip)
                                <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

