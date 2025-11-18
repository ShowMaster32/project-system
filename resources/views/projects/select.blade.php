@php($title = 'Seleziona Progetto')
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
            <p class="text-gray-600 mt-1">Scegli il progetto con cui lavorare. I dati e i permessi saranno filtrati automaticamente.</p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 rounded bg-green-50 border border-green-200 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($projects->isEmpty())
            <div class="rounded bg-white shadow p-6">
                <p class="text-gray-700">Non hai progetti disponibili. Contatta un amministratore.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach ($projects as $project)
                    <div class="rounded bg-white shadow p-5 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-gray-800">{{ $project->name }}</h2>
                                @if (isset($lastProjectId) && $lastProjectId === $project->id)
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">Ultimo usato</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Codice: {{ $project->code }}</p>
                            <p class="text-sm text-gray-600">Ruolo: <span class="font-medium">{{ ucfirst($project->pivot->role) }}</span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('projects.enter', ['project' => $project->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                    Entra
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-8 text-center text-sm text-gray-500">
            <a href="{{ route('filament.admin.auth.logout') }}" class="underline">Esci</a>
        </div>
    </div>
</body>
</html>
