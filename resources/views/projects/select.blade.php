<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleziona Portale — Project System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .project-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.10);
        }
        .project-card.last-used {
            border-color: #3b82f6;
        }
        .card-accent {
            width: 4px;
            border-radius: 4px 0 0 4px;
            flex-shrink: 0;
        }
        /* Scrollbar minimalista */
        .cards-grid::-webkit-scrollbar { width: 6px; }
        .cards-grid::-webkit-scrollbar-track { background: transparent; }
        .cards-grid::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <header class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-800 tracking-tight">Project System</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 hidden sm:block">
                    {{ auth()->user()->name }}
                </span>
                <a href="{{ route('filament.user.auth.logout') }}"
                   class="text-xs text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Esci
                </a>
            </div>
        </div>
    </header>

    {{-- ── Hero ────────────────────────────────────────────────────────── --}}
    <div class="max-w-6xl mx-auto px-6 pt-10 pb-6">
        <div class="mb-2">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Seleziona il portale</h1>
            <p class="text-sm text-gray-500 mt-1">Scegli il progetto con cui vuoi lavorare.</p>
        </div>

        {{-- Alert --}}
        @if (session('error'))
            <div class="mt-4 flex items-center gap-3 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mt-4 flex items-center gap-3 rounded-xl bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
    </div>

    {{-- ── Contenuto ───────────────────────────────────────────────────── --}}
    <div class="max-w-6xl mx-auto px-6 pb-16">

        @if ($projects->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700">Nessun portale disponibile</h2>
                <p class="text-sm text-gray-400 mt-1 max-w-xs">Non sei ancora associato ad alcun progetto. Contatta un amministratore.</p>
            </div>

        @else

            {{-- Search box (visibile con >4 progetti) --}}
            @if ($projects->count() > 4)
                <div class="mb-5 relative max-w-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="project-search"
                           placeholder="Cerca portale…"
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           oninput="filterProjects(this.value)">
                </div>
            @endif

            {{-- Griglia card --}}
            <div id="projects-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                @php
                    $palette = [
                        '#3b82f6','#8b5cf6','#10b981','#f59e0b',
                        '#ef4444','#06b6d4','#ec4899','#84cc16',
                        '#f97316','#6366f1','#14b8a6','#a855f7',
                    ];
                @endphp

                @foreach ($projects as $index => $project)
                    @php
                        $isLast  = isset($lastProjectId) && $lastProjectId === $project->id;
                        $color   = $palette[$index % count($palette)];
                        $role    = ucfirst(str_replace('_', ' ', $project->pivot->role ?? 'member'));
                        $memberCount = $project->users_count ?? null;
                    @endphp

                    <div class="project-card {{ $isLast ? 'last-used' : '' }} bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex"
                         data-name="{{ strtolower($project->name . ' ' . $project->code) }}">

                        {{-- Accent strip colorato --}}
                        <div class="card-accent" style="background: {{ $color }}"></div>

                        <div class="flex-1 p-5 flex flex-col justify-between">
                            <div>
                                {{-- Riga codice + badge last used --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-md text-white"
                                          style="background: {{ $color }}">
                                        {{ $project->code ?? '—' }}
                                    </span>
                                    @if ($isLast)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-medium border border-blue-100">
                                            ★ Ultimo usato
                                        </span>
                                    @endif
                                </div>

                                {{-- Nome progetto --}}
                                <h2 class="text-base font-semibold text-gray-900 leading-snug mb-1">
                                    {{ $project->name }}
                                </h2>

                                {{-- Descrizione --}}
                                @if ($project->description)
                                    <p class="text-xs text-gray-400 leading-relaxed line-clamp-2">
                                        {{ $project->description }}
                                    </p>
                                @endif
                            </div>

                            {{-- Footer card: ruolo + pulsante --}}
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-50">
                                <span class="text-xs text-gray-400">
                                    Ruolo: <span class="font-medium text-gray-600">{{ $role }}</span>
                                </span>

                                <form action="{{ route('projects.enter', ['project' => $project->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white transition-opacity hover:opacity-90 active:scale-95"
                                            style="background: {{ $color }}">
                                        Entra
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- Messaggio "nessun risultato" ricerca --}}
            <div id="no-results" class="hidden py-16 text-center">
                <p class="text-sm text-gray-400">Nessun portale corrisponde alla ricerca.</p>
            </div>

        @endif
    </div>

    <script>
    function filterProjects(query) {
        const q = query.trim().toLowerCase();
        const cards = document.querySelectorAll('#projects-grid .project-card');
        let visible = 0;
        cards.forEach(function(card) {
            const match = card.dataset.name.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const noRes = document.getElementById('no-results');
        if (noRes) noRes.classList.toggle('hidden', visible > 0);
    }
    </script>

</body>
</html>
