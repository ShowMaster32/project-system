<?php

namespace App\Console\Commands;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkPackage;
use App\Notifications\DeadlineApproachingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDeadlineReminders extends Command
{
    protected $signature   = 'pmt:send-deadline-reminders';
    protected $description = 'Invia notifiche per deadline imminenti (1 e 3 giorni)';

    /** Giorni prima della deadline per cui inviare reminder */
    private const REMINDER_DAYS = [1, 3];

    public function handle(): int
    {
        $today = Carbon::today();
        $this->info("Invio reminder deadline — {$today->format('d/m/Y')}");

        foreach (self::REMINDER_DAYS as $days) {
            $targetDate = $today->copy()->addDays($days);
            $this->processTaskDeadlines($targetDate, $days);
            $this->processWorkPackageDeadlines($targetDate, $days);
            $this->processMilestoneDeadlines($targetDate, $days);
        }

        // Invia anche per scadenze già oggi (daysLeft = 0)
        $this->processTaskDeadlines($today, 0);
        $this->processWorkPackageDeadlines($today, 0);
        $this->processMilestoneDeadlines($today, 0);

        $this->info('Reminder inviati correttamente.');

        return self::SUCCESS;
    }

    // ─── Tasks ───────────────────────────────────────────────────────────────

    private function processTaskDeadlines(Carbon $targetDate, int $daysLeft): void
    {
        $tasks = Task::whereDate('end_date', $targetDate)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['assignedUser', 'leader', 'workPackage'])
            ->get();

        foreach ($tasks as $task) {
            $recipients = $this->getTaskRecipients($task);
            $notification = new DeadlineApproachingNotification(
                subjectType: 'task',
                subjectId:   $task->id,
                subjectName: $task->name,
                subjectCode: $task->code ?? "T{$task->id}",
                projectId:   $task->project_id,
                endDate:     $task->end_date->format('d/m/Y'),
                daysLeft:    $daysLeft
            );

            foreach ($recipients as $user) {
                $user->notify($notification);
            }

            $this->line("  Task [{$task->name}] → {$recipients->count()} utenti notificati (daysLeft={$daysLeft})");
        }
    }

    // ─── Work Packages ────────────────────────────────────────────────────────

    private function processWorkPackageDeadlines(Carbon $targetDate, int $daysLeft): void
    {
        $workPackages = WorkPackage::whereDate('end_date', $targetDate)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('leader')
            ->get();

        foreach ($workPackages as $wp) {
            $recipients = collect();
            if ($wp->leader) {
                $recipients->push($wp->leader);
            }

            if ($recipients->isEmpty()) {
                continue;
            }

            $notification = new DeadlineApproachingNotification(
                subjectType: 'work_package',
                subjectId:   $wp->id,
                subjectName: $wp->name,
                subjectCode: $wp->code ?? "WP{$wp->id}",
                projectId:   $wp->project_id,
                endDate:     $wp->end_date->format('d/m/Y'),
                daysLeft:    $daysLeft
            );

            foreach ($recipients as $user) {
                $user->notify($notification);
            }

            $this->line("  WP [{$wp->name}] → {$recipients->count()} utenti notificati (daysLeft={$daysLeft})");
        }
    }

    // ─── Milestones ──────────────────────────────────────────────────────────

    private function processMilestoneDeadlines(Carbon $targetDate, int $daysLeft): void
    {
        $milestones = Milestone::whereDate('due_date', $targetDate)
            ->whereNull('completed_at')   // completed_at null = non completata
            ->with(['task.assignedUser', 'task.leader'])
            ->get();

        foreach ($milestones as $milestone) {
            $task = $milestone->task;
            if (! $task) {
                continue;
            }

            $recipients = $this->getTaskRecipients($task);
            if ($recipients->isEmpty()) {
                continue;
            }

            $notification = new DeadlineApproachingNotification(
                subjectType: 'milestone',
                subjectId:   $milestone->id,
                subjectName: $milestone->name,
                subjectCode: "M{$milestone->id}",
                projectId:   $task->project_id,
                endDate:     $milestone->due_date->format('d/m/Y'),
                daysLeft:    $daysLeft
            );

            foreach ($recipients as $user) {
                $user->notify($notification);
            }
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getTaskRecipients(Task $task): \Illuminate\Support\Collection
    {
        $ids = collect();

        if ($task->assigned_to) {
            $ids->push($task->assigned_to);
        }
        if ($task->leader_id && ! $ids->contains($task->leader_id)) {
            $ids->push($task->leader_id);
        }
        if ($task->workPackage?->leader_id && ! $ids->contains($task->workPackage->leader_id)) {
            $ids->push($task->workPackage->leader_id);
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $ids)->get();
    }
}
