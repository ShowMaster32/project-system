<?php

use App\Http\Controllers\ProjectSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Project Selection Routes (require auth)
Route::middleware('auth')->group(function () {
    Route::get('/projects/select', [ProjectSelectionController::class, 'select'])
        ->name('projects.select');
    
    Route::post('/projects/{project}/enter', [ProjectSelectionController::class, 'enter'])
        ->name('projects.enter');
    
    Route::post('/projects/switch', [ProjectSelectionController::class, 'switch'])
        ->name('projects.switch');
});