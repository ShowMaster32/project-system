<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly string $assignedBy = ''
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'task_assigned',
            'title'          => 'Task assegnato a te',
            'body'           => "Sei stato assegnato al task \"{$this->task->name}\"" .
                                ($this->assignedBy ? " da {$this->assignedBy}" : '') . '.',
            'task_id'        => $this->task->id,
            'task_name'      => $this->task->name,
            'task_code'      => $this->task->code ?? null,
            'project_id'     => $this->task->project_id,
            'end_date'       => $this->task->end_date?->format('d/m/Y'),
            'url'            => "/user/{$this->task->project_id}/tasks/{$this->task->id}/edit",
            'icon'           => 'heroicon-o-clipboard-document-check',
            'color'          => 'info',
        ];
    }
}
