<?php

use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\GanttController;
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

    // ── Gantt API routes ──────────────────────────────────────────────────
    Route::get('/gantt/data',               [GanttController::class, 'data'])->name('gantt.data');
    Route::put('/gantt/task/{id}',          [GanttController::class, 'updateTask'])->name('gantt.task.update');
    Route::post('/gantt/link',              [GanttController::class, 'createLink'])->name('gantt.link.create');
    Route::delete('/gantt/link/{source}/{target}', [GanttController::class, 'deleteLink'])->name('gantt.link.delete');

    // Document download/preview routes (autenticato, con check permessi interno)
    Route::get('/documents/{document}/download', [DocumentDownloadController::class, 'download'])
        ->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentDownloadController::class, 'preview'])
        ->name('documents.preview');
});
