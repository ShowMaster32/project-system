<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definizione permessi per gruppo
        $permissions = [
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

        // Crea tutti i permessi
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $name => $description) {
                Permission::create([
                    'name' => $name,
                    'guard_name' => 'web',
                    'group' => $group,
                    'description' => $description,
                ]);
            }
        }

        // Definizione ruoli con livelli e permessi
        $roles = [
            'super_admin' => [
                'level' => 100,
                'description' => 'Amministratore globale del sistema',
                'permissions' => ['*'], // Tutti i permessi (gestito via Gate::before)
            ],
            'project_admin' => [
                'level' => 90,
                'description' => 'Amministratore del progetto',
                'permissions' => array_keys(array_merge(...array_values($permissions))), // Tutti i permessi
            ],
            'coordinator' => [
                'level' => 70,
                'description' => 'Coordinatore del progetto',
                'permissions' => [
                    'projects.view',
                    'work_packages.view', 'work_packages.create', 'work_packages.edit', 'work_packages.assign',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.change_status',
                    'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.complete',
                    'deliverables.view', 'deliverables.create', 'deliverables.edit', 'deliverables.validate',
                    'documents.view', 'documents.upload', 'documents.edit', 'documents.download',
                    'users.view',
                    'reports.view', 'reports.export',
                ],
            ],
            'wp_leader' => [
                'level' => 50,
                'description' => 'Leader di Work Package',
                'permissions' => [
                    'projects.view',
                    'work_packages.view', 'work_packages.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.change_status',
                    'milestones.view', 'milestones.create', 'milestones.edit',
                    'deliverables.view', 'deliverables.create', 'deliverables.edit',
                    'documents.view', 'documents.upload', 'documents.download',
                    'users.view',
                    'reports.view',
                ],
            ],
            'task_leader' => [
                'level' => 40,
                'description' => 'Leader di Task',
                'permissions' => [
                    'projects.view',
                    'work_packages.view',
                    'tasks.view', 'tasks.edit', 'tasks.change_status',
                    'milestones.view',
                    'deliverables.view', 'deliverables.create', 'deliverables.edit',
                    'documents.view', 'documents.upload', 'documents.download',
                    'users.view',
                    'reports.view',
                ],
            ],
            'team_member' => [
                'level' => 20,
                'description' => 'Membro del team',
                'permissions' => [
                    'projects.view',
                    'work_packages.view',
                    'tasks.view', 'tasks.change_status',
                    'milestones.view',
                    'deliverables.view',
                    'documents.view', 'documents.upload', 'documents.download',
                    'users.view',
                ],
            ],
            'viewer' => [
                'level' => 10,
                'description' => 'Solo visualizzazione',
                'is_default' => true,
                'permissions' => [
                    'projects.view',
                    'work_packages.view',
                    'tasks.view',
                    'milestones.view',
                    'deliverables.view',
                    'documents.view', 'documents.download',
                    'users.view',
                    'reports.view',
                ],
            ],
        ];

        // Crea i ruoli e assegna i permessi
        foreach ($roles as $roleName => $config) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'description' => $config['description'],
                'level' => $config['level'],
                'is_default' => $config['is_default'] ?? false,
            ]);

            // Assegna permessi (tranne per super_admin che usa Gate::before)
            if ($roleName !== 'super_admin') {
                $role->givePermissionTo($config['permissions']);
            }
        }

        $this->command->info('✅ Ruoli e permessi creati con successo!');
        $this->command->info('');
        $this->command->info('📋 Ruoli creati:');
        foreach ($roles as $name => $config) {
            $permCount = $name === 'super_admin' ? 'ALL' : count($config['permissions']);
            $this->command->info("   - {$name} (Level: {$config['level']}, Permessi: {$permCount})");
        }
    }
}
