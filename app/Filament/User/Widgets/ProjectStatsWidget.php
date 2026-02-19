<?php

namespace App\Filament\User\Widgets;

use App\Models\Task;
use App\Models\WorkPackage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProjectStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $projectId = session('current_project_id');

        if (! $projectId) {
            return [
                Stat::make('Progetto', 'Nessun progetto selezionato')
                    ->color('gray')
                    ->icon('heroicon-o-information-circle'),
            ];
        }

        // ── Una sola query aggregate per i task ───────────────────────────
        $taskStats = Task::where('project_id', $projectId)
            ->selectRaw("
                COUNT(*)                                                        AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END)         AS completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END)       AS in_progress,
                SUM(CASE WHEN status NOT IN ('completed','cancelled')
                              AND end_date IS NOT NULL
                              AND end_date < CURRENT_DATE THEN 1 ELSE 0 END)  AS overdue,
                COALESCE(AVG(progress), 0)                                     AS avg_progress
            ")
            ->first();

        $totalTasks     = (int) ($taskStats->total ?? 0);
        $completedTasks = (int) ($taskStats->completed ?? 0);
        $inProgTasks    = (int) ($taskStats->in_progress ?? 0);
        $overdueTasks   = (int) ($taskStats->overdue ?? 0);
        $avgProgress    = (int) round($taskStats->avg_progress ?? 0);

        // ── Una sola query aggregate per i WP ─────────────────────────────
        $wpStats = WorkPackage::where('project_id', $projectId)
            ->selectRaw("
                COUNT(*)                                                      AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END)       AS completed
            ")
            ->first();

        $totalWps     = (int) ($wpStats->total ?? 0);
        $completedWps = (int) ($wpStats->completed ?? 0);

        return [
            Stat::make('Avanzamento globale', $avgProgress . '%')
                ->description("{$completedTasks} / {$totalTasks} task completati")
                ->color(match (true) {
                    $avgProgress >= 100 => 'success',
                    $avgProgress >= 50  => 'primary',
                    default             => 'warning',
                })
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Task in corso', (string) $inProgTasks)
                ->description("{$totalTasks} task totali nel progetto")
                ->color('info')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Work Package', "{$completedWps} / {$totalWps} completati")
                ->description($totalWps > 0 && $completedWps === $totalWps ? 'Tutti completati ✓' : '')
                ->color($totalWps > 0 && $completedWps === $totalWps ? 'success' : 'primary')
                ->icon('heroicon-o-rectangle-stack'),

            Stat::make('Task in ritardo', (string) $overdueTasks)
                ->description($overdueTasks > 0 ? 'Richiedono attenzione' : 'Nessun ritardo')
                ->color($overdueTasks > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
