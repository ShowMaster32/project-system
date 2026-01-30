<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $role = session('current_user_role');
        $isGlobalAdmin = auth()->user()?->isGlobalAdmin();

        // Solo admin del progetto o global admin possono creare utenti
        $canCreate = ($role === 'admin') || $isGlobalAdmin;

        return [
            Actions\CreateAction::make()
                ->label('New User')
                ->visible($canCreate),
        ];
    }

    public function getTitle(): string
    {
        return 'Users';
    }

    public function getSubheading(): ?string
    {
        $projectName = session('current_project_name', 'Current Project');
        return "Managing users for {$projectName}";
    }
}
