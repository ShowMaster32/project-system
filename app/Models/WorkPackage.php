<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkPackage extends Model
{
    use HasFactory, SoftDeletes, BelongsToProject;
    
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