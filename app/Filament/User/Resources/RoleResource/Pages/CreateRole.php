<?php

namespace App\Filament\User\Resources\RoleResource\Pages;

use App\Filament\User\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return 'Crea Nuovo Ruolo';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assicurati che il guard sia 'web'
        $data['guard_name'] = 'web';
        
        // Associa al progetto corrente se non è un ruolo globale
        // I ruoli custom sono sempre associati al progetto
        $data['project_id'] = session('current_project_id');
        
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Ruolo creato con successo')
            ->body("Il ruolo '{$this->record->name}' è stato creato.")
            ->success()
            ->send();
    }
}
