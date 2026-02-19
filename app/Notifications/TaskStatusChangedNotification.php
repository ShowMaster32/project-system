<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task   $task,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        public readonly string $changedBy = ''
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'not_started' => 'Non iniziato',
            'in_progress' => 'In corso',
            'completed'   => 'Completato',
            'on_hold'     => 'In pausa',
            'cancelled'   => 'Annullato',
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        $color = match ($this->newStatus) {
            'completed'   => 'success',
            'cancelled'   => 'danger',
            'on_hold'     => 'warning',
            'in_progress' => 'info',
            default       => 'gray',
        };

        return [
            'type'        => 'task_status_changed',
            'title'       => 'Stato task aggiornato',
            'body'        => "Il task \"{$this->task->name}\" è passato da \"{$oldLabel}\" a \"{$newLabel}\"" .
                             ($this->changedBy ? " da {$this->changedBy}" : '') . '.',
            'task_id'     => $this->task->id,
            'task_name'   => $this->task->name,
            'task_code'   => $this->task->code ?? null,
            'project_id'  => $this->task->project_id,
            'old_status'  => $this->oldStatus,
            'new_status'  => $this->newStatus,
            'url'         => "/user/{$this->task->project_id}/tasks/{$this->task->id}/edit",
            'icon'        => 'heroicon-o-arrow-path',
            'color'       => $color,
        ];
    }
}
