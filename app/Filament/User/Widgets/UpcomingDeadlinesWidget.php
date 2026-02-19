<?php

namespace App\Filament\User\Widgets;

use App\Models\Milestone;
use App\Models\Task;
use Filament\Widgets\Widget;

class UpcomingDeadlinesWidget extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.user.widgets.upcoming-deadlines';

    /** Quanti giorni avanti guardare */
    public int $lookahead = 30;

    public function getDeadlines(): array
    {
        $projectId = session('current_project_id');

        if (! $projectId) {
            return [];
        }

        $deadlines = [];
        $today     = now()->startOfDay();
        $limit     = now()->startOfDay()->addDays($this->lookahead);

        // ── Task con end_date nel range (non completati/cancellati) ──────
        Task::where('project_id', $projectId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $limit)
            ->with('workPackage:id,name,code')
            ->orderBy('end_date')
            ->get()
            ->each(function (Task $task) use (&$deadlines, $today) {
                $daysLeft  = $today->diffInDays($task->end_date->startOfDay(), false);
                $deadlines[] = [
                    'type'      => 'task',
                    'icon'      => 'heroicon-o-check-circle',
                    'name'      => ($task->code ? "[{$task->code}] " : '') . $task->name,
                    'context'   => $task->workPackage ? ($task->workPackage->code ? "[{$task->workPackage->code}] " : '') . $task->workPackage->name : '',
                    'due_date'  => $task->end_date->format('d/m/Y'),
                    'days_left' => $daysLeft,
                    'status'    => $task->status,
                    'progress'  => $task->progress ?? 0,
                    'sort_date' => $task->end_date->timestamp,
                ];
            });

        // ── Milestone con due_date nel range (non completate) ────────────
        Milestone::where('project_id', $projectId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $limit)
            ->with('workPackage:id,name,code')
            ->orderBy('due_date')
            ->get()
            ->each(function (Milestone $ms) use (&$deadlines, $today) {
                $daysLeft  = $today->diffInDays($ms->due_date->startOfDay(), false);
                $deadlines[] = [
                    'type'      => 'milestone',
                    'icon'      => 'heroicon-o-flag',
                    'name'      => ($ms->code ? "[{$ms->code}] " : '') . $ms->name,
                    'context'   => $ms->workPackage ? ($ms->workPackage->code ? "[{$ms->workPackage->code}] " : '') . $ms->workPackage->name : '',
                    'due_date'  => $ms->due_date->format('d/m/Y'),
                    'days_left' => $daysLeft,
                    'status'    => $ms->status,
                    'progress'  => null,
                    'sort_date' => $ms->due_date->timestamp,
                ];
            });

        // Ordina per data (prima i più vicini / scaduti)
        usort($deadlines, fn ($a, $b) => $a['sort_date'] <=> $b['sort_date']);

        return $deadlines;
    }
}
