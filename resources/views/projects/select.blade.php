<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleziona Progetto - Project System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">
                    Benvenuto, {{ auth()->user()->name }}!
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    Seleziona il progetto su cui vuoi lavorare
                </p>
            </div>

            <!-- Messages -->
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($projects as $project)
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow border-2 {{ $project->id === $lastProjectId ? 'border-blue-500' : 'border-transparent' }}">
                        <div class="p-6">
                            <!-- Badge per ultimo progetto -->
                            @if($project->id === $lastProjectId)
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mb-3">
                                    Ultimo accesso
                                </span>
                            @endif

                            <!-- Project Info -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                {{ $project->name }}
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-1">
                                <strong>Codice:</strong> {{ $project->code }}
                            </p>

                            @if($project->description)
                                <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                                    {{ $project->description }}
                                </p>
                            @endif

                            <!-- Role Badge -->
                            <div class="mb-4">
                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
                                    {{ ucfirst($project->pivot->role) }}
                                </span>
                            </div>

                            <!-- Enter Button -->
                            <form action="{{ route('projects.enter', $project) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                    Entra nel Progetto
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">
                            Non hai accesso a nessun progetto.
                        </p>
                        <p class="text-gray-400 text-sm mt-2">
                            Contatta l'amministratore per ottenere l'accesso.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Logout -->
            <div class="text-center pt-4">
                <form action="{{ route('filament.admin.auth.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-gray-900 text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>