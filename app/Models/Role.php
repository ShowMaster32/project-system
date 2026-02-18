<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'project_id',
        'description',
        'is_default',
        'level',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Relazione con il progetto (per ruoli project-scoped)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Scope per ruoli globali (senza project_id)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }

    /**
     * Scope per ruoli di un progetto specifico
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope per ruoli disponibili (globali + progetto corrente)
     */
    public function scopeAvailable($query, $projectId = null)
    {
        $projectId = $projectId ?? session('current_project_id');
        
        return $query->where(function ($q) use ($projectId) {
            $q->whereNull('project_id')
              ->orWhere('project_id', $projectId);
        });
    }

    /**
     * Verifica se è un ruolo globale
     */
    public function isGlobal(): bool
    {
        return is_null($this->project_id);
    }

    /**
     * Verifica se è il ruolo super_admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->name === 'super_admin' && $this->isGlobal();
    }

    /**
     * Ottieni il nome visualizzabile
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->description ?? ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * Gerarchia ruoli - ritorna true se questo ruolo è superiore all'altro
     */
    public function isHigherThan(Role $other): bool
    {
        return $this->level > $other->level;
    }

    /**
     * Definizione livelli ruoli standard
     */
    public static function getRoleLevels(): array
    {
        return [
            'super_admin' => 100,
            'project_admin' => 90,
            'coordinator' => 70,
            'wp_leader' => 50,
            'task_leader' => 40,
            'team_member' => 20,
            'viewer' => 10,
        ];
    }

    /**
     * Mappa i ruoli legacy (dal pivot project_user) ai nuovi ruoli Spatie
     */
    public static function mapLegacyRole(string $legacyRole): string
    {
        return match($legacyRole) {
            'admin' => 'project_admin',
            'user' => 'team_member',
            default => $legacyRole, // coordinator, wp_leader, task_leader rimangono uguali
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (!$role->level) {
                $levels = self::getRoleLevels();
                $role->level = $levels[$role->name] ?? 0;
            }
        });
    }
}
