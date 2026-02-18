<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group',
        'description',
    ];

    /**
     * Scope per filtrare per gruppo
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Ottieni tutti i gruppi di permessi disponibili
     */
    public static function getGroups(): array
    {
        return self::query()
            ->whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Ottieni il nome visualizzabile
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $parts = explode('.', $this->name);
        return collect($parts)
            ->map(fn($part) => ucfirst(str_replace('_', ' ', $part)))
            ->join(' - ');
    }

    /**
     * Ottieni il nome dell'azione (es: 'create' da 'tasks.create')
     */
    public function getActionAttribute(): string
    {
        $parts = explode('.', $this->name);
        return end($parts);
    }

    /**
     * Ottieni il nome della risorsa (es: 'tasks' da 'tasks.create')
     */
    public function getResourceAttribute(): string
    {
        $parts = explode('.', $this->name);
        return $parts[0] ?? '';
    }

    /**
     * Permessi raggruppati per visualizzazione nella UI
     */
    public static function getAllGrouped(): array
    {
        return self::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            ->toArray();
    }

    /**
     * Definizione di tutti i permessi del sistema
     */
    public static function getSystemPermissions(): array
    {
        return [
            'projects' => [
                'projects.view' => 'Visualizzare progetti',
                'projects.create' => 'Creare progetti',
                'projects.edit' => 'Modificare progetti',
                'projects.delete' => 'Eliminare progetti',
                'projects.manage_users' => 'Gestire utenti del progetto',
            ],
            'work_packages' => [
                'work_packages.view' => 'Visualizzare work packages',
                'work_packages.create' => 'Creare work packages',
                'work_packages.edit' => 'Modificare work packages',
                'work_packages.delete' => 'Eliminare work packages',
                'work_packages.assign' => 'Assegnare leader ai WP',
            ],
            'tasks' => [
                'tasks.view' => 'Visualizzare task',
                'tasks.create' => 'Creare task',
                'tasks.edit' => 'Modificare task',
                'tasks.delete' => 'Eliminare task',
                'tasks.assign' => 'Assegnare utenti ai task',
                'tasks.change_status' => 'Cambiare stato dei task',
            ],
            'milestones' => [
                'milestones.view' => 'Visualizzare milestone',
                'milestones.create' => 'Creare milestone',
                'milestones.edit' => 'Modificare milestone',
                'milestones.delete' => 'Eliminare milestone',
                'milestones.complete' => 'Completare milestone',
            ],
            'deliverables' => [
                'deliverables.view' => 'Visualizzare deliverable',
                'deliverables.create' => 'Creare deliverable',
                'deliverables.edit' => 'Modificare deliverable',
                'deliverables.delete' => 'Eliminare deliverable',
                'deliverables.validate' => 'Validare deliverable',
            ],
            'documents' => [
                'documents.view' => 'Visualizzare documenti',
                'documents.upload' => 'Caricare documenti',
                'documents.edit' => 'Modificare documenti',
                'documents.delete' => 'Eliminare documenti',
                'documents.download' => 'Scaricare documenti',
            ],
            'users' => [
                'users.view' => 'Visualizzare utenti',
                'users.create' => 'Creare utenti',
                'users.edit' => 'Modificare utenti',
                'users.delete' => 'Eliminare utenti',
                'users.change_role' => 'Cambiare ruolo utenti',
            ],
            'reports' => [
                'reports.view' => 'Visualizzare report',
                'reports.export' => 'Esportare report',
            ],
        ];
    }
}
