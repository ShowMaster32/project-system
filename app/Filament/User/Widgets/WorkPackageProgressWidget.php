<?php

namespace App\Filament\User\Widgets;

use App\Models\WorkPackage;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class WorkPackageProgressWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.user.widgets.work-package-progress';

    public function getWorkPackages(): array
    {
        $projectId = session('current_project_id');

        if (! $projectId) {
            return [];
        }

        $statusColors = [
            'not_started' => '#94a3b8',
            'in_progress' => '#3b82f6',
            'completed'   => '#22c55e',
            'on_hold'     => '#f59e0b',
        ];

        $statusLabels = [
            'not_started' => 'Non avviato',
            'in_progress' => 'In corso',
            'completed'   => 'Completato',
            'on_hold'     => 'In attesa',
        ];

        // ── Una query con eager load task — zero N+1 ──────────────────────
        $wps = WorkPackage::where('project_id', $projectId)
            ->orderBy('start_date')
            ->with(['tasks:id,work_package_id,status,progress'])
            ->get();

        return $wps->map(function (WorkPackage $wp) use ($statusColors, $statusLabels) {

            // Aggrega da collection in memoria — nessuna query extra
            $tasks          = $wp->tasks;
            $totalTasks     = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed')->count();
            $inProgTasks    = $tasks->where('status', 'in_progress')->count();
            $blockedTasks   = $tasks->where('status', 'blocked')->count();
            $avgProgress    = $totalTasks > 0
                ? (int) round($tasks->avg('progress'))
                : ($wp->progress ?? 0);

            // Giorni rimanenti
            $daysLeft  = null;
            $isOverdue = false;
            if ($wp->end_date) {
                $daysLeft  = now()->startOfDay()->diffInDays($wp->end_date->startOfDay(), false);
                $isOverdue = $daysLeft < 0 && $wp->status !== 'completed';
            }

            return [
                'id'              => $wp->id,
                'code'            => $wp->code ?? '',
                'name'            => $wp->name,
                'status'          => $wp->status ?? 'not_started',
                'status_label'    => $statusLabels[$wp->status ?? 'not_started'] ?? $wp->status,
                'status_color'    => $statusColors[$wp->status ?? 'not_started'] ?? '#94a3b8',
                'progress'        => $avgProgress,
                'total_tasks'     => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_prog_tasks'   => $inProgTasks,
                'blocked_tasks'   => $blockedTasks,
                'end_date'        => $wp->end_date?->format('d/m/Y'),
                'days_left'       => $daysLeft,
                'is_overdue'      => $isOverdue,
            ];
        })->toArray();
    }
}
