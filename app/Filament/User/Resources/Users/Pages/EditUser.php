<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Modifica Utente: ' . $this->record->name;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        $isGlobalAdmin    = auth()->user()?->isGlobalAdmin();
        $canManage        = auth()->user()?->hasProjectPermission('users.change_role') || $isGlobalAdmin;
        $canChangePass    = auth()->user()?->hasProjectPermission('users.edit') || $isGlobalAdmin;
        $currentProjectId = session('current_project_id');

        return [
            // ── Cambio ruolo nel progetto ──────────────────────────────────
            Actions\Action::make('changeRole')
                ->label('Cambia ruolo nel progetto')
                ->icon('heroicon-o-user-circle')
                ->color('warning')
                ->visible(fn () => $canManage && $currentProjectId)
                ->form([
                    Select::make('role')
                        ->label('Ruolo nel progetto corrente')
                        ->options([
                            'project_admin' => 'Project Admin',
                            'coordinator'   => 'Coordinatore',
                            'wp_leader'     => 'WP Leader',
                            'task_leader'   => 'Task Leader',
                            'team_member'   => 'Team Member',
                            'viewer'        => 'Visualizzatore',
                        ])
                        ->default(fn () => $this->record->getRoleInProject($currentProjectId))
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) use ($currentProjectId) {
                    $this->record->projects()->updateExistingPivot($currentProjectId, [
                        'role' => $data['role'],
                    ]);

                    Notification::make()
                        ->title('Ruolo aggiornato')
                        ->body("Ruolo cambiato in: {$data['role']}")
                        ->success()
                        ->send();

                    // In Filament 4.x usa $this->redirect() non redirect() globale
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            // ── Cambio password ────────────────────────────────────────────
            Actions\Action::make('changePassword')
                ->label('Cambia password')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->visible(fn () => $canChangePass)
                ->form([
                    TextInput::make('new_password')
                        ->label('Nuova password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),   // cerca automaticamente new_password_confirmation

                    TextInput::make('new_password_confirmation')
                        ->label('Conferma nuova password')
                        ->password()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->forceFill([
                        'password' => Hash::make($data['new_password']),
                    ])->save();

                    Notification::make()
                        ->title('Password aggiornata')
                        ->success()
                        ->send();
                }),

            // ── Elimina utente ─────────────────────────────────────────────
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasProjectPermission('users.delete') || $isGlobalAdmin)
                ->requiresConfirmation()
                ->modalHeading('Elimina utente')
                ->modalDescription('Sei sicuro di voler eliminare questo utente? L\'operazione non è reversibile.')
                ->modalSubmitActionLabel('Sì, elimina')
                ->successNotificationTitle('Utente eliminato'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Utente aggiornato con successo';
    }
}
