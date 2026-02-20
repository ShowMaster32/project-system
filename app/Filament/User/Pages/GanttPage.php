<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;

class GanttPage extends Page
{
    protected string $view = 'filament.user.pages.gantt';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Gantt';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $slug            = 'gantt';

    public function getTitle(): string
    {
        $project = session('current_project_name', 'Progetto');
        return "Gantt — {$project}";
    }

    // Verifica accesso: solo chi ha almeno tasks.view
    public static function canAccess(): bool
    {
        return auth()->user()?->hasProjectPermission('tasks.view') ?? false;
    }
}
