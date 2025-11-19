<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Edit User';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        $canManage = session('current_user_role') === 'admin' || auth()->user()?->isGlobalAdmin();

        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $canManage),

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
                        ->same('new_password_confirmation'),
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
        ];
    }
}
