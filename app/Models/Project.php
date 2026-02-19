<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('project')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Progetto \"{$this->name}\" creato",
                'updated' => "Progetto \"{$this->name}\" aggiornato",
                'deleted' => "Progetto \"{$this->name}\" eliminato",
                default   => "Progetto \"{$this->name}\" {$eventName}",
            });
    }
    
    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'logo_path',
        'settings',
        'is_active',
    ];
    
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    
    // Relazioni
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role', 'is_active')
                    ->withTimestamps();
    }
    
    public function workPackages()
    {
        return $this->hasMany(WorkPackage::class);
    }
    
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
    
    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}