<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\WorkPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GanttController extends Controller
{
    /**
     * Offset per separare gli ID dei WP da quelli dei Task nel flat-list DHTMLX.
     * WP con id=1  → DHTMLX id = 100001
     * Task con id=1 → DHTMLX id = 1
     */
    const WP_OFFSET = 100000;

    // ──── Autenticazione di base ──────────────────────────────────────────

    private function checkAuth(): int
    {
        abort_unless(auth()->check(), 401);
        $projectId = session('current_project_id');
        abort_unless($projectId, 403, 'Nessun progetto selezionato.');
        return (int) $projectId;
    }

    // ──── GET /gantt/data ─────────────────────────────────────────────────

    public function data()
    {
        $projectId = $this->checkAuth();

        $tasks  = [];
        $links  = [];
        $linkId = 1;

        // ── Work Packages come righe padre (tipo "project" = summary bar) ──
        WorkPackage::where('project_id', $projectId)
            ->orderBy('start_date')
            ->get()
            ->each(function (WorkPackage $wp) use (&$tasks) {
                $start = $wp->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
                $end   = $wp->end_date?->format('Y-m-d')   ?? now()->addDays(30)->format('Y-m-d');
                $dur   = $wp->duration_days ?? max(1, Carbon::parse($start)->diffInDays(Carbon::parse($end)));

                $tasks[] = [
                    'id'       => self::WP_OFFSET + $wp->id,
                    'text'     => ($wp->code ? "[{$wp->code}] " : '') . $wp->name,
                    'start_date' => $start,
                    'end_date'   => $end,
                    'duration'   => (int) $dur,
                    'progress'   => round(($wp->progress ?? 0) / 100, 2),
                    'open'       => true,
                    'type'       => 'project',   // summary bar DHTMLX
                    'readonly'   => true,
                    'color'      => $wp->color ?? '#64748b',
                ];
            });

        // ── Task come figli del WP corrispondente ─────────────────────────
        Task::where('project_id', $projectId)
            ->orderBy('start_date')
            ->get()
            ->each(function (Task $task) use (&$tasks, &$links, &$linkId) {
                $start = $task->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
                $end   = $task->end_date?->format('Y-m-d')   ?? now()->addDays(7)->format('Y-m-d');
                $dur   = $task->duration_days ?? max(1, Carbon::parse($start)->diffInDays(Carbon::parse($end)));

                // Colore: rosso per percorso critico, altrimenti custom o default
                $color = $task->color ?? ($task->is_critical_path ? '#ef4444' : '#3b82f6');

                $tasks[] = [
                    'id'         => $task->id,
                    'text'       => ($task->code ? "[{$task->code}] " : '') . $task->name,
                    'start_date' => $start,
                    'end_date'   => $end,
                    'duration'   => (int) $dur,
                    'progress'   => round(($task->progress ?? 0) / 100, 2),
                    'parent'     => $task->work_package_id
                                        ? self::WP_OFFSET + $task->work_package_id
                                        : 0,
                    'color'      => $color,
                    'textColor'  => '#ffffff',
                    'status'     => $task->status,
                    'critical'   => (bool) $task->is_critical_path,
                ];

                // Dipendenze → frecce Gantt (finish-to-start)
                if (! empty($task->depends_on) && is_array($task->depends_on)) {
                    foreach ($task->depends_on as $depId) {
                        if ($depId) {
                            $links[] = [
                                'id'     => $linkId++,
                                'source' => (int) $depId,
                                'target' => $task->id,
                                'type'   => '0', // finish-to-start
                            ];
                        }
                    }
                }
            });

        return response()->json(['data' => $tasks, 'links' => $links]);
    }

    // ──── PUT /gantt/task/{id} ────────────────────────────────────────────

    public function updateTask(Request $request, int $id)
    {
        $projectId = $this->checkAuth();

        // Le righe WP sono readonly → ignora silenziosamente
        if ($id >= self::WP_OFFSET) {
            return response()->json(['action' => 'updated', 'tid' => $id]);
        }

        abort_unless(
            auth()->user()?->hasProjectPermission('tasks.edit'),
            403,
            'Non hai i permessi per modificare i task.'
        );

        $task = Task::where('project_id', $projectId)->findOrFail($id);

        $data = [];

        if ($request->filled('start_date')) {
            $data['start_date'] = Carbon::parse($request->start_date)->startOfDay();
        }
        if ($request->filled('end_date')) {
            $data['end_date'] = Carbon::parse($request->end_date)->startOfDay();
        }
        if ($request->filled('duration')) {
            $data['duration_days'] = (int) $request->duration;
            // Ricalcola end_date da start + duration se non arriva end_date
            if (! isset($data['end_date']) && isset($data['start_date'])) {
                $data['end_date'] = $data['start_date']->copy()->addDays($data['duration_days']);
            } elseif (! isset($data['end_date']) && $task->start_date) {
                $data['end_date'] = $task->start_date->copy()->addDays($data['duration_days']);
            }
        }
        if ($request->has('progress')) {
            $data['progress'] = (int) round((float) $request->progress * 100);
        }

        if (! empty($data)) {
            $task->update($data);
        }

        return response()->json(['action' => 'updated', 'tid' => $id]);
    }

    // ──── POST /gantt/link  (create link → persiste in depends_on) ────────

    public function createLink(Request $request)
    {
        $projectId = $this->checkAuth();

        abort_unless(
            auth()->user()?->hasProjectPermission('tasks.edit'),
            403
        );

        $targetId = (int) $request->target;
        $sourceId = (int) $request->source;

        if ($targetId < self::WP_OFFSET && $sourceId < self::WP_OFFSET) {
            $task    = Task::where('project_id', $projectId)->findOrFail($targetId);
            $current = $task->depends_on ?? [];
            if (! in_array($sourceId, $current)) {
                $current[] = $sourceId;
                $task->update(['depends_on' => $current]);
            }
        }

        // Ritorna un ID link fittizio (non usiamo una tabella dedicata)
        return response()->json(['action' => 'inserted', 'tid' => time()]);
    }

    // ──── DELETE /gantt/link/{source}/{target} ────────────────────────────

    public function deleteLink(Request $request, int $source, int $target)
    {
        $projectId = $this->checkAuth();

        abort_unless(
            auth()->user()?->hasProjectPermission('tasks.edit'),
            403
        );

        if ($target < self::WP_OFFSET && $source < self::WP_OFFSET) {
            $task    = Task::where('project_id', $projectId)->findOrFail($target);
            $current = array_filter($task->depends_on ?? [], fn ($id) => (int) $id !== $source);
            $task->update(['depends_on' => array_values($current)]);
        }

        return response()->json(['action' => 'deleted']);
    }
}
