<?php

use App\Http\Controllers\ProjectSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Porta gli utenti alla dashboard del pannello User per impostazione predefinita.
    // Filament gestirà l'autenticazione e il redirect a /app/login se necessario.
    return redirect()->route('filament.user.pages.dashboard');
});

// Project Selection Routes (require auth)
Route::middleware('auth')->group(function () {
    Route::get('/projects/select', [ProjectSelectionController::class, 'select'])
        ->name('projects.select');

    Route::post('/projects/{project}/enter', [ProjectSelectionController::class, 'enter'])
        ->name('projects.enter');

    // Supporta anche GET per evitare 419 quando si accede direttamente via URL
    Route::get('/projects/{project}/enter', [ProjectSelectionController::class, 'enter'])
        ->name('projects.enter.get');

    Route::post('/projects/switch', [ProjectSelectionController::class, 'switch'])
        ->name('projects.switch');
});
