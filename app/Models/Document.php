<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, BelongsToProject;
    
    protected $fillable = [
        'project_id',
        'documentable_type',
        'documentable_id',
        'parent_id',
        'is_folder',
        'name',
        'file_path',
        'mime_type',
        'size_bytes',
        'version',
        'is_latest_version',
        'uploaded_by',
    ];
    
    protected $casts = [
        'is_folder' => 'boolean',
        'is_latest_version' => 'boolean',
        'size_bytes' => 'integer',
    ];
    
    public function documentable()
    {
        return $this->morphTo();
    }
    
    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Document::class, 'parent_id');
    }
    
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}