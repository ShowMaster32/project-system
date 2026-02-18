<?php

namespace App\Filament\User\Resources\RoleResource\Pages;

use App\Filament\User\Resources\RoleResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return 'Modifica Ruolo: ' . ucfirst(str_replace('_', ' ', $this->record->name));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        $isSystemRole = in_array($this->record->name, ['super_admin', 'project_admin', 'coordinator', 'wp_leader', 'task_leader', 'team_member', 'viewer']);

        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(!$isSystemRole)
                ->requiresConfirmation()
                ->modalHeading('Elimina Ruolo')
                ->modalDescription('Sei sicuro di voler eliminare questo ruolo? Gli utenti con questo ruolo perderanno i permessi associati.')
                ->modalSubmitActionLabel('Sì, elimina'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Ruolo aggiornato con successo';
    }

    protected function beforeSave(): void
    {
        // Previeni modifica nome ruoli di sistema
        $systemRoles = ['super_admin', 'project_admin', 'coordinator', 'wp_leader', 'task_leader', 'team_member', 'viewer'];
        
        if (in_array($this->record->getOriginal('name'), $systemRoles)) {
            if ($this->data['name'] !== $this->record->getOriginal('name')) {
                Notification::make()
                    ->title('Errore')
                    ->body('Non puoi modificare il nome di un ruolo di sistema.')
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }
    }
}
