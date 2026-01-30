<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Create New User';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assicurati che l'utente sia attivo di default
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $currentProjectId = session('current_project_id');
        $currentProjectName = session('current_project_name', 'project');
        
        // Associa automaticamente l'utente al progetto corrente con ruolo 'user'
        if ($currentProjectId) {
            $this->record->projects()->attach($currentProjectId, [
                'role' => 'user',
                'is_active' => true,
            ]);

            Notification::make()
                ->title('User created successfully')
                ->body("User has been added to {$currentProjectName} with role 'user'")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('User created')
                ->body('User created but not assigned to any project')
                ->warning()
                ->send();
        }
    }
}
