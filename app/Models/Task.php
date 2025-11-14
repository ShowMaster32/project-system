<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, BelongsToProject;
    
    protected $fillable = [
        'project_id',
        'work_package_id',
        'parent_id',
        'code',
        'name',
        'description',
        'leader_id',
        'assigned_to',
        'start_date',
        'end_date',
        'duration_days',
        'depends_on',
        'status',
        'progress',
        'color',
        'is_critical_path',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'depends_on' => 'array',
        'progress' => 'integer',
        'is_critical_path' => 'boolean',
    ];
    
    public function workPackage()
    {
        return $this->belongsTo(WorkPackage::class);
    }
    
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }
    
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
    
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
}