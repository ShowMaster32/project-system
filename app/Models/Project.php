<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    
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