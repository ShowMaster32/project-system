<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $projectId = session('current_project_id');
        
        if ($projectId) {
            $builder->where($model->getTable() . '.project_id', $projectId);
        }
    }
}