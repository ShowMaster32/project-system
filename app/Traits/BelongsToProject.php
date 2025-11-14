<?php

namespace App\Traits;

use App\Scopes\ProjectScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToProject
{
    protected static function bootBelongsToProject()
    {
        // Global Scope automatico
        static::addGlobalScope(new ProjectScope);
        
        // Auto-set project_id al creating
        static::creating(function (Model $model) {
            if (!$model->project_id) {
                $model->project_id = session('current_project_id');
            }
        });
    }
    
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}