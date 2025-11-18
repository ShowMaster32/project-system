<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\WorkPackage;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Crea 3 progetti di test
        $project1 = Project::create([
            'code' => 'PROJ-001',
            'name' => 'ArtemisSpace Alpha',
            'slug' => 'artemisspace-alpha',
            'description' => 'Progetto di ricerca spaziale Alpha',
            'is_active' => true,
            'settings' => [
                'features' => [
                    'gantt_enabled' => true,
                    'validation_workflow' => true,
                ],
            ],
        ]);

        $project2 = Project::create([
            'code' => 'PROJ-002',
            'name' => 'BioTech Beta',
            'slug' => 'biotech-beta',
            'description' => 'Progetto biotecnologie Beta',
            'is_active' => true,
        ]);

        $project3 = Project::create([
            'code' => 'PROJ-003',
            'name' => 'GreenEnergy Gamma',
            'slug' => 'greenenergy-gamma',
            'description' => 'Progetto energie rinnovabili Gamma',
            'is_active' => true,
        ]);

        // Trova il tuo admin user
        $admin = User::where('email', 'LIKE', '%')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Crea altri 2 utenti di test
        $user1 = User::create([
            'name' => 'Mario Rossi',
            'email' => 'mario@test.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Laura Bianchi',
            'email' => 'laura@test.com',
            'password' => bcrypt('password'),
        ]);

        // Assegna utenti ai progetti (N:N con ruoli)
        // Admin ha accesso a tutti i progetti come admin
        $admin->projects()->attach($project1->id, ['role' => 'admin', 'is_active' => true]);
        $admin->projects()->attach($project2->id, ['role' => 'admin', 'is_active' => true]);
        $admin->projects()->attach($project3->id, ['role' => 'admin', 'is_active' => true]);
        
        // Imposta last_project_id per admin
        $admin->update(['last_project_id' => $project1->id]);

        // Mario ha accesso a project1 e project2
        $user1->projects()->attach($project1->id, ['role' => 'coordinator', 'is_active' => true]);
        $user1->projects()->attach($project2->id, ['role' => 'user', 'is_active' => true]);
        $user1->update(['last_project_id' => $project1->id]);

        // Laura ha accesso solo a project1
        $user2->projects()->attach($project1->id, ['role' => 'wp_leader', 'is_active' => true]);
        $user2->update(['last_project_id' => $project1->id]);

        // Crea Work Packages per Project 1
        $wp1 = WorkPackage::create([
            'project_id' => $project1->id,
            'code' => 'WP1',
            'name' => 'WP1 - Sensor Development',
            'description' => 'Sviluppo sensori spaziali',
            'leader_id' => $user1->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'duration_days' => 180,
            'status' => 'active',
            'progress' => 25,
            'color' => '#3b82f6',
        ]);

        $wp2 = WorkPackage::create([
            'project_id' => $project1->id,
            'code' => 'WP2',
            'name' => 'WP2 - Testing & Validation',
            'description' => 'Test e validazione sistemi',
            'leader_id' => $user2->id,
            'start_date' => now()->addMonths(6),
            'end_date' => now()->addMonths(12),
            'duration_days' => 180,
            'status' => 'active',
            'progress' => 0,
            'color' => '#10b981',
        ]);

        // Crea Tasks per WP1
        Task::create([
            'project_id' => $project1->id,
            'work_package_id' => $wp1->id,
            'code' => 'T1.1',
            'name' => 'T1.1 - Requirements Analysis',
            'description' => 'Analisi requisiti sensori',
            'leader_id' => $user1->id,
            'assigned_to' => $user2->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(2),
            'duration_days' => 60,
            'status' => 'in_progress',
            'progress' => 40,
        ]);

        Task::create([
            'project_id' => $project1->id,
            'work_package_id' => $wp1->id,
            'code' => 'T1.2',
            'name' => 'T1.2 - Prototype Design',
            'description' => 'Design prototipo',
            'leader_id' => $user1->id,
            'start_date' => now()->addMonths(2),
            'end_date' => now()->addMonths(4),
            'duration_days' => 60,
            'status' => 'pending',
            'progress' => 0,
            'depends_on' => [], // Dipende da T1.1
        ]);

        // Crea WP per Project 2 (per testare isolamento)
        WorkPackage::create([
            'project_id' => $project2->id,
            'code' => 'WP1',
            'name' => 'WP1 - Research Phase',
            'description' => 'Fase di ricerca biotecnologie',
            'leader_id' => $user1->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'duration_days' => 90,
            'status' => 'active',
            'progress' => 15,
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('');
        $this->command->info('👤 Users created:');
        $this->command->info("   Admin: {$admin->email} / password");
        $this->command->info("   Mario: mario@test.com / password");
        $this->command->info("   Laura: laura@test.com / password");
        $this->command->info('');
        $this->command->info('📁 Projects created:');
        $this->command->info("   - {$project1->name} (ID: {$project1->id})");
        $this->command->info("   - {$project2->name} (ID: {$project2->id})");
        $this->command->info("   - {$project3->name} (ID: {$project3->id})");
    }
}