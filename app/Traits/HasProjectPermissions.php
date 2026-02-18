<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasProjectPermissions
 * 
 * Estende le funzionalità di Spatie HasRoles per supportare
 * la verifica dei permessi nel contesto del progetto corrente.
 * Mantiene compatibilità con i ruoli esistenti nel pivot project_user.
 */
trait HasProjectPermissions
{
    /**
     * Verifica se l'utente ha un permesso nel contesto del progetto corrente
     * 
     * Ordine di verifica:
     * 1. Super admin → accesso totale
     * 2. Global admin (da config) → accesso totale
     * 3. Permesso Spatie diretto
     * 4. Ruolo Spatie con quel permesso
     * 5. Fallback al ruolo legacy nel pivot project_user
     */
    public function hasProjectPermission(string $permission, ?int $projectId = null): bool
    {
        $projectId = $projectId ?? session('current_project_id');

        // 1. Super admin Spatie
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // 2. Global admin da config
        if ($this->isGlobalAdmin()) {
            return true;
        }

        // 3. Verifica permesso Spatie (con team/project context)
        if ($projectId) {
            // Imposta il team per Spatie
            setPermissionsTeamId($projectId);
        }

        // Verifica permesso diretto o via ruolo Spatie
        if ($this->can($permission)) {
            return true;
        }

        // 4. Fallback al sistema legacy (pivot project_user)
        return $this->hasLegacyPermission($permission, $projectId);
    }

    /**
     * Verifica permesso usando il sistema legacy (pivot project_user)
     * per retrocompatibilità durante la transizione
     */
    protected function hasLegacyPermission(string $permission, ?int $projectId): bool
    {
        if (!$projectId) {
            return false;
        }

        $legacyRole = $this->getRoleInProject($projectId);
        
        if (!$legacyRole) {
            return false;
        }

        // Mappa i permessi ai ruoli legacy
        $rolePermissions = $this->getLegacyRolePermissions($legacyRole);
        
        return in_array($permission, $rolePermissions);
    }

    /**
     * Ottieni i permessi associati a un ruolo legacy
     */
    protected function getLegacyRolePermissions(string $role): array
    {
        $allPermissions = [
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_users',
            'work_packages.view', 'work_packages.create', 'work_packages.edit', 'work_packages.delete', 'work_packages.assign',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign', 'tasks.change_status',
            'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.delete', 'milestones.complete',
            'deliverables.view', 'deliverables.create', 'deliverables.edit', 'deliverables.delete', 'deliverables.validate',
            'documents.view', 'documents.upload', 'documents.edit', 'documents.delete', 'documents.download',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.change_role',
            'reports.view', 'reports.export',
        ];

        return match($role) {
            'admin' => $allPermissions,
            'coordinator' => [
                'projects.view',
                'work_packages.view', 'work_packages.create', 'work_packages.edit', 'work_packages.assign',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.change_status',
                'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.complete',
                'deliverables.view', 'deliverables.create', 'deliverables.edit', 'deliverables.validate',
                'documents.view', 'documents.upload', 'documents.edit', 'documents.download',
                'users.view',
                'reports.view', 'reports.export',
            ],
            'wp_leader' => [
                'projects.view',
                'work_packages.view', 'work_packages.edit',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.change_status',
                'milestones.view', 'milestones.create', 'milestones.edit',
                'deliverables.view', 'deliverables.create', 'deliverables.edit',
                'documents.view', 'documents.upload', 'documents.download',
                'users.view',
                'reports.view',
            ],
            'task_leader' => [
                'projects.view',
                'work_packages.view',
                'tasks.view', 'tasks.edit', 'tasks.change_status',
                'milestones.view',
                'deliverables.view', 'deliverables.create', 'deliverables.edit',
                'documents.view', 'documents.upload', 'documents.download',
                'users.view',
                'reports.view',
            ],
            'user' => [
                'projects.view',
                'work_packages.view',
                'tasks.view', 'tasks.change_status',
                'milestones.view',
                'deliverables.view',
                'documents.view', 'documents.upload', 'documents.download',
                'users.view',
            ],
            default => [
                'projects.view',
                'work_packages.view',
                'tasks.view',
                'milestones.view',
                'deliverables.view',
                'documents.view', 'documents.download',
                'users.view',
                'reports.view',
            ],
        };
    }

    /**
     * Verifica se l'utente può gestire un altro utente (basato sulla gerarchia ruoli)
     */
    public function canManageUser($targetUser, ?int $projectId = null): bool
    {
        $projectId = $projectId ?? session('current_project_id');

        if ($this->isGlobalAdmin() || $this->hasRole('super_admin')) {
            return true;
        }

        if (!$projectId) {
            return false;
        }

        $myRole = $this->getRoleInProject($projectId);
        $targetRole = $targetUser->getRoleInProject($projectId);

        if (!$myRole || !$targetRole) {
            return false;
        }

        $levels = Role::getRoleLevels();
        $myLevel = $levels[Role::mapLegacyRole($myRole)] ?? 0;
        $targetLevel = $levels[Role::mapLegacyRole($targetRole)] ?? 0;

        return $myLevel > $targetLevel;
    }

    /**
     * Verifica se l'utente è admin del progetto corrente
     */
    public function isProjectAdmin(?int $projectId = null): bool
    {
        $projectId = $projectId ?? session('current_project_id');

        if ($this->isGlobalAdmin() || $this->hasRole('super_admin')) {
            return true;
        }

        if (!$projectId) {
            return false;
        }

        $role = $this->getRoleInProject($projectId);
        
        return $role === 'admin' || $this->hasRole('project_admin');
    }

    /**
     * Ottieni il livello del ruolo dell'utente nel progetto
     */
    public function getRoleLevel(?int $projectId = null): int
    {
        $projectId = $projectId ?? session('current_project_id');

        if ($this->hasRole('super_admin')) {
            return 100;
        }

        if ($this->isGlobalAdmin()) {
            return 100;
        }

        if (!$projectId) {
            return 0;
        }

        $role = $this->getRoleInProject($projectId);
        
        if (!$role) {
            return 0;
        }

        $levels = Role::getRoleLevels();
        $mappedRole = Role::mapLegacyRole($role);
        
        return $levels[$mappedRole] ?? 0;
    }

    /**
     * Ottieni tutti i permessi dell'utente nel progetto corrente
     */
    public function getProjectPermissions(?int $projectId = null): array
    {
        $projectId = $projectId ?? session('current_project_id');

        // Super admin o global admin hanno tutti i permessi
        if ($this->hasRole('super_admin') || $this->isGlobalAdmin()) {
            return array_keys(array_merge(...array_values(
                \App\Models\Permission::getSystemPermissions()
            )));
        }

        // Raccogli permessi Spatie
        if ($projectId) {
            setPermissionsTeamId($projectId);
        }
        
        $spatiePermissions = $this->getAllPermissions()->pluck('name')->toArray();

        // Aggiungi permessi legacy
        $legacyRole = $this->getRoleInProject($projectId);
        $legacyPermissions = $legacyRole ? $this->getLegacyRolePermissions($legacyRole) : [];

        return array_unique(array_merge($spatiePermissions, $legacyPermissions));
    }

    /**
     * Verifica se l'utente ha almeno uno dei permessi specificati
     */
    public function hasAnyProjectPermission(array $permissions, ?int $projectId = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasProjectPermission($permission, $projectId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se l'utente ha tutti i permessi specificati
     */
    public function hasAllProjectPermissions(array $permissions, ?int $projectId = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasProjectPermission($permission, $projectId)) {
                return false;
            }
        }
        return true;
    }
}
