<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Milestone extends Model
{
    use HasFactory, BelongsToProject, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'due_date', 'completed_at', 'leader_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('milestone')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Milestone \"{$this->name}\" creata",
                'updated' => "Milestone \"{$this->name}\" aggiornata",
                'deleted' => "Milestone \"{$this->name}\" eliminata",
                default   => "Milestone \"{$this->name}\" {$eventName}",
            });
    }
    
    protected $fillable = [
        'project_id',
        'work_package_id',
        'task_id',
        'code',
        'name',
        'description',
        'leader_id',
        'due_date',
        'completed_at',
        'status',
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];
    
    public function workPackage()
    {
        return $this->belongsTo(WorkPackage::class);
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }
}