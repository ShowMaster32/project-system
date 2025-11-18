<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">{{ $this->getProjectInfo()['name'] }}</h3>
                    <p class="text-sm text-gray-500">Codice: {{ $this->getProjectInfo()['code'] }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $this->getProjectInfo()['role'] }}
                    </span>
                </div>
            </div>
            
            <div class="pt-4 border-t">
                <a href="{{ route('projects.select') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Cambia progetto →
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
