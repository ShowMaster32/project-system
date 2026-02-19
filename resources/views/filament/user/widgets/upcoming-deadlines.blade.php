<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Scadenze prossime</x-slot>
        <x-slot name="headerEnd">
            <span class="text-xs text-gray-400">Prossimi {{ $this->lookahead }} giorni</span>
        </x-slot>

        @php $deadlines = $this->getDeadlines(); @endphp

        @if(empty($deadlines))
            <div class="py-8 text-center">
                <div class="text-4xl mb-2">✅</div>
                <p class="text-sm text-gray-500">Nessuna scadenza nei prossimi {{ $this->lookahead }} giorni.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($deadlines as $item)
                    @php
                        $isOverdue  = $item['days_left'] < 0;
                        $isUrgent   = $item['days_left'] >= 0 && $item['days_left'] <= 7;
                        $rowBg      = $isOverdue ? 'bg-red-50 dark:bg-red-950' : ($isUrgent ? 'bg-amber-50 dark:bg-amber-950' : '');
                        $dateBadge  = $isOverdue
                            ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                            : ($isUrgent ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'
                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400');
                    @endphp

                    <div class="flex items-center gap-3 py-3 px-2 rounded {{ $rowBg }}">
                        {{-- Icona tipo --}}
                        <div class="flex-shrink-0">
                            @if($item['type'] === 'milestone')
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300 text-xs">🏁</span>
                            @else
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 text-xs">✓</span>
                            @endif
                        </div>

                        {{-- Nome + WP di appartenenza --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">
                                {{ $item['name'] }}
                            </p>
                            @if($item['context'])
                                <p class="text-xs text-gray-400 truncate">{{ $item['context'] }}</p>
                            @endif
                        </div>

                        {{-- Progress (solo task) --}}
                        @if($item['progress'] !== null)
                            <div class="hidden sm:flex items-center gap-1 w-24">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full bg-blue-500"
                                         style="width: {{ $item['progress'] }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 w-8 text-right">{{ $item['progress'] }}%</span>
                            </div>
                        @else
                            <div class="hidden sm:block w-24"></div>
                        @endif

                        {{-- Data scadenza + giorni --}}
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $dateBadge }}">
                                {{ $item['due_date'] }}
                            </span>
                            <p class="text-xs mt-0.5
                                {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold'
                                    : ($isUrgent ? 'text-amber-600 dark:text-amber-400'
                                    : 'text-gray-400') }}">
                                @if($isOverdue)
                                    {{ abs($item['days_left']) }}gg di ritardo
                                @elseif($item['days_left'] === 0)
                                    Oggi!
                                @else
                                    tra {{ $item['days_left'] }}gg
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
