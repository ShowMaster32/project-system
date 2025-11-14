<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliverable extends Model
{
    use HasFactory, BelongsToProject;
    
    protected $fillable = [
        'project_id',
        'work_package_id',
        'task_id',
        'milestone_id',
        'code',
        'name',
        'description',
        'responsible_id',
        'due_date',
        'delivered_at',
        'status',
        'requires_validation',
        'validated_by',
        'validated_at',
        'validation_notes',
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'delivered_at' => 'datetime',
        'validated_at' => 'datetime',
        'requires_validation' => 'boolean',
    ];
    
    public function workPackage()
    {
        return $this->belongsTo(WorkPackage::class);
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }
    
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
    
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}