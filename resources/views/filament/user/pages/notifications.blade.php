<x-filament-panels::page>
    @php
        $notifications = $this->getNotifications();
    @endphp

    <div class="space-y-3" wire:poll.30s>

        @if($notifications->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-bell-slash class="w-16 h-16 mb-4 opacity-40"/>
                <p class="text-lg font-medium">Nessuna notifica</p>
                <p class="text-sm mt-1">Sei in pari con tutto!</p>
            </div>
        @else
            @foreach($notifications as $n)
                <div
                    class="relative flex gap-4 rounded-xl border p-4 shadow-sm transition-all duration-200
                           {{ $n['read']
                               ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'
                               : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' }}"
                >
                    {{-- Pallino non-letto --}}
                    @unless($n['read'])
                        <span class="absolute top-4 right-4 inline-flex h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                    @endunless

                    {{-- Icona colorata --}}
                    <div class="flex-shrink-0 mt-0.5">
                        @php
                            $colorClasses = match($n['color']) {
                                'success' => 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400',
                                'danger'  => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
                                'warning' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
                                'info'    => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
                                default   => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                            };
                        @endphp
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $colorClasses }}">
                            <x-dynamic-component :component="$n['icon']" class="w-5 h-5"/>
                        </div>
                    </div>

                    {{-- Contenuto --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $n['title'] }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                            {{ $n['body'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5" title="{{ $n['created'] }}">
                            {{ $n['time'] }}
                        </p>
                    </div>

                    {{-- Azioni --}}
                    <div class="flex-shrink-0 flex flex-col gap-1 items-end">
                        @if($n['url'])
                            <a
                                href="{{ $n['url'] }}"
                                wire:click="markRead('{{ $n['id'] }}')"
                                class="text-xs font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200"
                            >
                                Vai →
                            </a>
                        @endif

                        @unless($n['read'])
                            <button
                                wire:click="markRead('{{ $n['id'] }}')"
                                class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                title="Segna come letto"
                            >
                                Letto
                            </button>
                        @endunless

                        <button
                            wire:click="deleteNotification('{{ $n['id'] }}')"
                            wire:confirm="Eliminare questa notifica?"
                            class="text-xs text-red-400 hover:text-red-600"
                            title="Elimina"
                        >
                            <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</x-filament-panels::page>
