<?php

namespace App\Models;

use App\Traits\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes, BelongsToProject;

    // Categorie disponibili
    const CATEGORIES = [
        'deliverable'  => 'Deliverable',
        'report'       => 'Report',
        'contract'     => 'Contratto',
        'presentation' => 'Presentazione',
        'minutes'      => 'Verbale',
        'other'        => 'Altro',
    ];

    protected $fillable = [
        'project_id',
        'documentable_type',
        'documentable_id',
        'parent_id',
        'is_folder',
        'name',
        'category',
        'file_path',
        'mime_type',
        'size_bytes',
        'version',
        'is_latest_version',
        'uploaded_by',
    ];

    protected $casts = [
        'is_folder'         => 'boolean',
        'is_latest_version' => 'boolean',
        'size_bytes'        => 'integer',
    ];

    // ──── Observer per auto-popolamento metadata ────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            // Auto-set uploader
            if (! $document->uploaded_by) {
                $document->uploaded_by = auth()->id();
            }
            $document->is_folder          = false;
            $document->is_latest_version  = true;
            $document->version            = 1;

            // Legge mime_type e size direttamente dallo storage
            if ($document->file_path) {
                $disk = Storage::disk('documents');
                if ($disk->exists($document->file_path)) {
                    $document->size_bytes = $disk->size($document->file_path);
                    $document->mime_type  = $disk->mimeType($document->file_path);
                }
            }
        });
    }

    // ──── Relazioni ────────────────────────────────────────────────────────

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

    // ──── Helper ──────────────────────────────────────────────────────────

    /** Dimensione leggibile (es. "2.4 MB") */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size_bytes) {
            return '—';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size_bytes;
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    /** Icona Heroicon in base al mime_type */
    public function getIconAttribute(): string
    {
        $mime = $this->mime_type ?? '';

        if (str_starts_with($mime, 'image/')) {
            return 'heroicon-o-photo';
        }
        if ($mime === 'application/pdf') {
            return 'heroicon-o-document-text';
        }
        if (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'heroicon-o-document';
        }
        if (in_array($mime, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return 'heroicon-o-table-cells';
        }

        return 'heroicon-o-paper-clip';
    }

    /** True se il file è un'immagine visualizzabile in anteprima */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /** True se il file è un PDF visualizzabile inline */
    public function getIsPdfAttribute(): bool
    {
        return ($this->mime_type ?? '') === 'application/pdf';
    }

    /** Etichetta categoria */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
