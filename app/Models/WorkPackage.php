<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkPackage extends Model
{
    use HasFactory, SoftDeletes, BelongsToProject, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'progress', 'leader_id', 'end_date', 'start_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('work_package')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Work Package \"{$this->name}\" creato",
                'updated' => "Work Package \"{$this->name}\" aggiornato",
                'deleted' => "Work Package \"{$this->name}\" eliminato",
                default   => "Work Package \"{$this->name}\" {$eventName}",
            });
    }
    
    protected $fillable = [
        'project_id',
        'code',
        'name',
        'description',
        'leader_id',
        'start_date',
        'end_date',
        'duration_days',
        'status',
        'progress',
        'color',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
    ];
    
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }
    
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
}