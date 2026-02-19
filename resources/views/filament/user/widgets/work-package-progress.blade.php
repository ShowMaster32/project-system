<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Avanzamento Work Package</x-slot>
        <x-slot name="headerEnd">
            <span class="text-xs text-gray-400">Progresso medio dei task per WP</span>
        </x-slot>

        @php $wps = $this->getWorkPackages(); @endphp

        @if(empty($wps))
            <p class="text-sm text-gray-500 py-4 text-center">Nessun Work Package trovato per questo progetto.</p>
        @else
            <div class="space-y-4">
                @foreach($wps as $wp)
                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900">

                        {{-- Riga titolo + badge status + scadenza --}}
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                @if($wp['code'])
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                        {{ $wp['code'] }}
                                    </span>
                                @endif
                                <span class="font-medium text-sm text-gray-800 dark:text-gray-100">
                                    {{ $wp['name'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                {{-- Badge status --}}
                                <span class="px-2 py-0.5 rounded-full text-white text-xs font-medium"
                                      style="background-color: {{ $wp['status_color'] }}">
                                    {{ $wp['status_label'] }}
                                </span>
                                {{-- Scadenza --}}
                                @if($wp['end_date'])
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $wp['is_overdue'] ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                                            : ($wp['days_left'] <= 7 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                        📅 {{ $wp['end_date'] }}
                                        @if($wp['is_overdue'])
                                            ({{ abs($wp['days_left']) }}gg ritardo)
                                        @elseif($wp['days_left'] !== null)
                                            ({{ $wp['days_left'] }}gg)
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Barra progresso --}}
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full transition-all duration-500"
                                     style="width: {{ $wp['progress'] }}%;
                                            background-color: {{ $wp['status'] === 'completed' ? '#22c55e'
                                                : ($wp['is_overdue'] ? '#ef4444' : '#3b82f6') }}">
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 w-10 text-right">
                                {{ $wp['progress'] }}%
                            </span>
                        </div>

                        {{-- Contatori task --}}
                        @if($wp['total_tasks'] > 0)
                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                    {{ $wp['completed_tasks'] }} completati
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                    {{ $wp['in_prog_tasks'] }} in corso
                                </span>
                                @if($wp['blocked_tasks'] > 0)
                                    <span class="flex items-center gap-1">
                                        <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                                        {{ $wp['blocked_tasks'] }} bloccati
                                    </span>
                                @endif
                                <span class="ml-auto">{{ $wp['total_tasks'] }} task totali</span>
                            </div>
                        @else
                            <p class="mt-2 text-xs text-gray-400 italic">Nessun task assegnato</p>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
