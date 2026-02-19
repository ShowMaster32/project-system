<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DeadlineApproachingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $subjectType  'task' | 'work_package' | 'milestone'
     * @param int    $subjectId
     * @param string $subjectName
     * @param string $subjectCode
     * @param int    $projectId
     * @param string $endDate      formatted d/m/Y
     * @param int    $daysLeft
     */
    public function __construct(
        public readonly string $subjectType,
        public readonly int    $subjectId,
        public readonly string $subjectName,
        public readonly string $subjectCode,
        public readonly int    $projectId,
        public readonly string $endDate,
        public readonly int    $daysLeft
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabel = match ($this->subjectType) {
            'work_package' => 'Work Package',
            'milestone'    => 'Milestone',
            default        => 'Task',
        };

        $urgency = $this->daysLeft <= 1
            ? 'URGENTE — scade oggi!'
            : "scade tra {$this->daysLeft} giorni ({$this->endDate})";

        $color = $this->daysLeft <= 1 ? 'danger' : ($this->daysLeft <= 3 ? 'warning' : 'info');

        $urlSegment = match ($this->subjectType) {
            'work_package' => 'work-packages',
            'milestone'    => 'milestones',
            default        => 'tasks',
        };

        return [
            'type'         => 'deadline_approaching',
            'title'        => "Deadline imminente — {$typeLabel}",
            'body'         => "{$typeLabel} \"{$this->subjectName}\" ({$this->subjectCode}) {$urgency}.",
            'subject_type' => $this->subjectType,
            'subject_id'   => $this->subjectId,
            'subject_name' => $this->subjectName,
            'subject_code' => $this->subjectCode,
            'project_id'   => $this->projectId,
            'end_date'     => $this->endDate,
            'days_left'    => $this->daysLeft,
            'url'          => "/user/{$this->projectId}/{$urlSegment}/{$this->subjectId}/edit",
            'icon'         => 'heroicon-o-clock',
            'color'        => $color,
        ];
    }
}
