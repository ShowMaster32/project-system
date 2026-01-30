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
        return 'Edit User: ' . $this->record->name;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        $role = session('current_user_role');
        $isGlobalAdmin = auth()->user()?->isGlobalAdmin();
        $canManage = ($role === 'admin') || $isGlobalAdmin;
        $currentProjectId = session('current_project_id');

        return [
            // Azione per cambiare il ruolo dell'utente nel progetto corrente
            Actions\Action::make('changeRole')
                ->label('Change Project Role')
                ->icon('heroicon-o-user-circle')
                ->visible(fn () => $canManage && $currentProjectId)
                ->form([
                    Select::make('role')
                        ->label('Role in Current Project')
                        ->options([
                            'admin' => 'Admin',
                            'coordinator' => 'Coordinator',
                            'wp_leader' => 'Work Package Leader',
                            'task_leader' => 'Task Leader',
                            'user' => 'User',
                        ])
                        ->default(fn () => $this->record->getRoleInProject($currentProjectId))
                        ->required(),
                ])
                ->action(function (array $data) use ($currentProjectId) {
                    $this->record->projects()->updateExistingPivot($currentProjectId, [
                        'role' => $data['role'],
                    ]);

                    Notification::make()
                        ->title('Role updated successfully')
                        ->body("User role changed to {$data['role']}")
                        ->success()
                        ->send();

                    // Refresh la pagina per aggiornare i dati
                    redirect()->to($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            // Azione per cambiare password
            Actions\Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->visible(fn () => $canManage)
                ->form([
                    TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->same('new_password_confirmation')
                        ->validationMessages([
                            'same' => 'Passwords do not match',
                        ]),
                    TextInput::make('new_password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->forceFill([
                        'password' => Hash::make($data['new_password']),
                    ])->save();

                    Notification::make()
                        ->title('Password updated successfully')
                        ->success()
                        ->send();
                }),

            // Azione per eliminare l'utente
            Actions\DeleteAction::make()
                ->visible(fn () => $canManage)
                ->requiresConfirmation()
                ->modalHeading('Delete User')
                ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete user')
                ->successNotificationTitle('User deleted successfully'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'User updated successfully';
    }
}
