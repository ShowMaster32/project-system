<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskStatusChangedNotification;

class TaskObserver
{
    /**
     * Task creato → notifica all'utente assegnato.
     */
    public function created(Task $task): void
    {
        $this->notifyAssigned($task, null);
    }

    /**
     * Task aggiornato → notifica cambio assegnazione o cambio stato.
     */
    public function updated(Task $task): void
    {
        // Cambio utente assegnato
        if ($task->wasChanged('assigned_to')) {
            $this->notifyAssigned($task, $task->getOriginal('assigned_to'));
        }

        // Cambio stato
        if ($task->wasChanged('status')) {
            $this->notifyStatusChange(
                $task,
                $task->getOriginal('status') ?? 'not_started',
                $task->status
            );
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function notifyAssigned(Task $task, ?int $previousAssignedId): void
    {
        if (! $task->assigned_to) {
            return;
        }

        // Non notificare se il task è assegnato alla stessa persona
        if ($previousAssignedId === $task->assigned_to) {
            return;
        }

        $assignee = User::find($task->assigned_to);
        if (! $assignee) {
            return;
        }

        $assignedByName = auth()->check() ? auth()->user()->name : '';

        // Non inviare la notifica a se stesso se è l'autore
        if (auth()->id() === $task->assigned_to) {
            return;
        }

        $assignee->notify(new TaskAssignedNotification($task, $assignedByName));
    }

    private function notifyStatusChange(Task $task, string $oldStatus, string $newStatus): void
    {
        $changedByName = auth()->check() ? auth()->user()->name : '';

        $notifiedUsers = collect();

        // Notifica il responsabile del task (leader)
        if ($task->leader_id && $task->leader_id !== auth()->id()) {
            $leader = User::find($task->leader_id);
            if ($leader) {
                $leader->notify(new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $changedByName));
                $notifiedUsers->push($task->leader_id);
            }
        }

        // Notifica l'utente assegnato (se diverso dal leader e dall'autore)
        if ($task->assigned_to
            && $task->assigned_to !== auth()->id()
            && ! $notifiedUsers->contains($task->assigned_to)
        ) {
            $assignee = User::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $changedByName));
                $notifiedUsers->push($task->assigned_to);
            }
        }

        // Notifica il WP leader (tramite workPackage->leader_id)
        $task->loadMissing('workPackage');
        $wpLeaderId = $task->workPackage?->leader_id;

        if ($wpLeaderId
            && $wpLeaderId !== auth()->id()
            && ! $notifiedUsers->contains($wpLeaderId)
        ) {
            $wpLeader = User::find($wpLeaderId);
            if ($wpLeader) {
                $wpLeader->notify(new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $changedByName));
            }
        }
    }
}
