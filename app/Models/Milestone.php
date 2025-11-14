<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory, BelongsToProject;
    
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